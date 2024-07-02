<?php

namespace App\Services;

use App\Exceptions\SmsMessageCreateException;

/**
 * Bussiness logic for creating a message
 */
class CreateMessageService{
  private string $message;
  private string $sender_id;
  private string $phone_number;
  private string $user_id;
  private array $errors;
  private \App\Models\Message $createdMessage;

  function __construct(
    string $message,
    string $sender_id,
    string $phone_number,
    string $user_id
  ){
    $this->message = $message;
    $this->sender_id = $sender_id;
    $this->phone_number =$phone_number;
    $this->user_id = $user_id;

    $this->validateParameters();
  }

  public function createMessage(): bool{
    try{
      $this->createdMessage = \App\Models\Message::create([
        'message' => $this->message,
        'phone_number' => $this->phone_number,
        'sender_id' => $this->sender_id,
        'user_id' => $this->user_id
      ]);

      return true;
    }
    catch (ValidationException $e){
      $this->errors = $e->errors();

      return false;
    }
  }

  public function createdMessage(): array{
    return [
      'message' => $this->createdMessage['message'],
      'phone_number' => $this->createdMessage['phone_number'],
      'sender_id' => $this->createdMessage['sender_id'],
      'sms_provider_id' => $this->createdMessage['sms_provider_id'],
      'message_status_id' => $this->createdMessage['message_status_id']
    ];
  }

  public function errors(): array{
    return $this->errors;
  }

  private function validateParameters(){
    // throw new SmsMessageCreateException("Provided parameters are invalid. Cannot create message.");
  }
}
