<?php

namespace App\UseCases;

use App\Models\Message;
use App\Models\MessageStatus;
use App\Services\SendMessageService;
use App\Services\CreateMessageService;
use App\Services\Types\MessageToSendStruct;
use App\Services\Types\MessageToSendStructType;
use App\Services\Types\MessageToSendSmsProviderStruct;
use App\Services\Types\MessageToSendSmsProviderStructType;
use App\Repositories\BadwordRepository;
use App\Jobs\SendSMSMessageToProviderJob;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

interface SendMessageUseCaseInterface{
  public function dispatch(): bool;
  public function messageOutputReloaded(): MessageToSendStructType;
}

/**
 * Send Message Use Case.
 * Must be used from outer layers only, whenever an SMS message has to be sent.
 * Tightly couple on inner layers only, and has the reponsibilites to create a new
 * message, and dispatch an external API request to the SMS provider, to send it.
 */
class SendMessageUseCase implements SendMessageUseCaseInterface{
  private MessageToSendStructType $message;
  private MessageToSendSmsProviderStructType $smsProvider;
  private array $errors;

  public function __construct(
    string $message_content,
    string $sender_id,
    string $phone_number,
    string $user_id
  ){
    $this->message_content = $message_content;
    $this->sender_id = $sender_id;
    $this->phone_number =$phone_number;
    $this->user_id = $user_id;

    //
    // TODO: We may need to repeat or apply additional validation here.
    //
  }

  public function dispatch(): bool{
    // Validate, create a message and save it to the database.
    if ($this->createMessage() === false){
      return false;
    }

    //
    // Send message to the SMS provider, to further dispatch it to the user phone.
    //
    if ($this->sendMessage() === false){
      return false;
    }

    return true;
  }

  /**
   * Message output.
   * Has to be reloaded to be updated with the current state,
   * after dispatching operations in the servicses.
   */
  public function messageOutputReloaded(): MessageToSendStructType{
    // Reload message
    $message = Message::find($this->message->data()['message_id']);

    return new MessageToSendStruct(
      message_id: $message['id'],
      message: $message['message'],
      phone_number: $message['phone_number'],
      sender_id: $message['sender_id'],
      sms_provider_id: $message['sms_provider_id'],
      message_status_id: $message['message_status_id']
    );
  }

  public function errors(){
    return $this->errors;
  }

  private function createMessage(): bool{
    // TODO: Use DI here.
    $badwordRepository = new BadwordRepository();

    // We could use DI here.
    $createMessageService = new CreateMessageService(
      message_content: $this->message_content,
      phone_number: $this->phone_number,
      sender_id: $this->sender_id,
      user_id: $this->user_id,
      badwordRepository: $badwordRepository
    );

    if($createMessageService->createMessage() === true){
      $this->message = $createMessageService->message();
      $this->smsProvider = $createMessageService->smsProvider();

      return true;
    }

    $this->errors = $createMessageService->errors();
    return false;
  }

  private function sendMessage(): bool{
    if(env('QUEUE_ENABLED') === true){
      return $this->sendMessageAsync();
    }

    return $this->sendMessageSync();
  }

  /**
   * Send the message and wait for a response from the SMS provider.
   */
  private function sendMessageSync(): bool{
    $sendMessageService = new SendMessageService($this->message, $this->smsProvider);

    if($sendMessageService->send() === true){
      return true;
    }

    $this->errors = $sendMessageService->errors();
    return false;
  }

  /**
   * Create a job in the queue to send the message to the SMS provider.
   */
  private function sendMessageAsync(): bool{
    // Load the message from the database to update status.
    $message = Message::find($this->message->data()['message_id']);
    $status_id = MessageStatus::where('status', 'queued_for_dispatch_to_the_sms_provider')->first()->id;
    $message->message_status_id = $status_id;
    $message->save();

    $this->messageOutputReloaded();

    SendSMSMessageToProviderJob::dispatch($this->message, $this->smsProvider);
    return true;
  }
}
