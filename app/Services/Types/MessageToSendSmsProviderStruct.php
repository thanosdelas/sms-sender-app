<?php

namespace App\Services\Types;

class MessageToSendSmsProviderStruct implements MessageToSendSmsProviderStructType{
  private $provider;
  private $details;
  private $api_url;
  private $sms_max_limit_per_day;

  public function __construct(
    string $provider,
    string $details,
    string $api_url,
    string $sms_max_limit_per_day
  ){
    $this->provider = $provider;
    $this->details =$details;
    $this->api_url = $api_url;
    $this->sms_max_limit_per_day = $sms_max_limit_per_day;
  }

  public function data(): array{
    return [
      'provider' => $this->provider,
      'details' => $this->details,
      'api_url' => $this->api_url,
      'sms_max_limit_per_day' => $this->sms_max_limit_per_day
    ];
  }
}
