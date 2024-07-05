<?php

namespace App\UseCases;

use App\Services\SendMessageService;
use App\Services\CreateMessageService;
use App\Services\Types\MessageToSendStructType;

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
  // private \App\Models\Message $createdMessage;
  // private array $message;
  private MessageToSendStructType $message;

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
    // TODO: We may need to repeate or apply additional validation here.
    //
    // $this->validateMessage();
  }

  public function dispatch(): bool{
    if ($this->createMessage() === false){
      return false;
    }

    // Send message to the SMS provider, to further dispatch it to the user phone.

    return true;
  }

  public function message(): MessageToSendStructType{
    return $this->message;
  }

  private function createMessage(): bool{
    $createMessageService = new CreateMessageService(
      message_content: $this->message_content,
      phone_number: $this->phone_number,
      sender_id: $this->sender_id,
      user_id: $this->user_id
    );

    if($createMessageService->createMessage() === true){
      $this->message = $createMessageService->message();

      return true;
    }

    $this->errors = $createMessageService->errors();
    return false;
  }
}
