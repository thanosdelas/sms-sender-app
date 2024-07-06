<?php

namespace App\Services\Types;

class MessageToSendStruct implements MessageToSendStructType{
  private $message_id;
  private $phone_number;
  private $sender_id;
  private $sms_provider_id;
  private $message_status_id;

  public function __construct(
    string $message_id,
    string $message,
    string $phone_number,
    string $sender_id,
    string $sms_provider_id,
    string $message_status_id
  ){
    $this->message_id = $message_id;
    $this->message = $message;
    $this->phone_number =$phone_number;
    $this->sender_id = $sender_id;
    $this->sms_provider_id = $sms_provider_id;
    $this->message_status_id = $message_status_id;
  }

  public function data(): array{
    return [
      'message_id' => $this->message_id,
      'message' => $this->message,
      'phone_number' => $this->phone_number,
      'sender_id' => $this->sender_id,
      'sms_provider_id' => $this->sms_provider_id,
      'message_status_id' => $this->message_status_id
    ];
  }
}
