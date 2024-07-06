<?php

namespace App\UseCases;

use App\Services\SendMessageService;
use App\Services\CreateMessageService;
use App\Services\Types\MessageToSendStructType;
use App\Services\Types\MessageToSendSmsProviderStructType;
use App\Repositories\BadwordRepository;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

interface SendMessageUseCaseInterface{
  public function dispatch(): bool;
  public function message(): MessageToSendStructType;
}

/**
 * Send Message Use Case.
 * Must be used from outer layers only, whenever an SMS message has to be sent.
 * Tightly couple only on inner layers, and has the reponsibilites to create a new
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
    // $this->validateMessage();
  }

  public function dispatch(): bool{
    // Validate, create a message and save it to the database.
    if ($this->createMessage() === false){
      return false;
    }

    // Send message to the SMS provider, to further dispatch it to the user phone.
    if ($this->sendMessage() === false){
      return false;
    }

    return true;
  }

  public function message(): MessageToSendStructType{
    return $this->message;
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
    $sendMessageService = new SendMessageService($this->message, $this->smsProvider);

    if($sendMessageService->send() === true){

      // var_dump("\n\nSuccess\n\n");
      // var_dump($sendMessageService);
      // exit();

      return true;
    }

    // var_dump("\n\nFailed\n\n");
    // var_dump($sendMessageService->errors());
    // exit();

    $this->errors = $sendMessageService->errors();
    return false;
  }
}
