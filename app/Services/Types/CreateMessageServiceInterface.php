<?php

namespace App\Services\Types;

interface CreateMessageServiceInterface{
  public function createMessage(): bool;
  public function message(): MessageToSendStructType;
}
