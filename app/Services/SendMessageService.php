<?php

namespace App\Services;
use App\Services\Types\MessageToSendStructType;
use App\Services\Types\MessageToSendSmsProviderStructType;

use Illuminate\Support\Facades\Http;
use App\Exceptions\SmsMessageCreateException;
use Illuminate\Support\Facades\Config;

/**
 * Send an SMS message to the API of the SMS provider.
 * TODO: We should probably defer the actual request to the SMS provider
 *       API, to a job, put it inside a queue, and process the response elsewhere.
 */
class SendMessageService{
  private array $responseMessage;
  private string $accessToken;
  private string $smsProviderApiURL;
  private MessageToSendStructType $message;
  private MessageToSendSmsProviderStructType $smsProvider;
  private array $errors;

  public function __construct(
    MessageToSendStructType $message,
    MessageToSendSmsProviderStructType $smsProvider
  ){
    $this->message = $message;
    $this->smsProvider = $smsProvider;
  }

  public function responseMessage(){
    return $this->responseMessage;
  }

  public function errors(): array{
    return $this->errors;
  }

  public function send(): bool{
    //
    // TODO: Add validation here, and return is is empty
    //
    // NOTE: The access_token for each user and sms provider could
    //       have been stored in the database, but for the sake of this
    //       technical assignment, we load it from the ENV.
    $this->accessToken = env('APP_SMS_TO_PROVIDER_ACCESS_TOKEN');


    $this->smsProviderApiURL = $this->smsProvider->data()['api_url'];

    // You can use the following URLs for dummy api to make it fail.
    // $this->smsProviderApiURL = 'https://example.com/';
    // $this->smsProviderApiURL = 'https://dummyjson.com/posts/add';

    // Send the message to the SMS provider.
    $response = $this->sendSMSMessage();

    // var_dump($response);
    // exit();

    if ($response['status_code'] !== 200 && $response['status_code'] !== 201){
      $this->errors = [
        'status_code' => $response['status_code'],
        'message' => $response['body']['message']
      ];

      return false;
    }



    // var_dump("HERE");
    // var_dump($response['body']);
    // exit();


    $this->responseMessage = $response['body'];
    return true;
  }

  private function sendSMSMessage(){
    $headers = [
      'Authorization' => "Bearer $this->accessToken",
      'Content-Type' => 'application/json',
    ];

    $response = Http::withHeaders($headers)->post($this->smsProviderApiURL, [
      'message' => $this->message->data()['message'],
      'to' => $this->message->data()['phone_number'],
      'sender_id' => $this->message->data()['sender_id'],
      // NOTE: The following should be configured in the database or the ENV.
      //       We leave them hardcoded for this assignment`.
      'bypass_optout' => true,
      'callback_url' => 'localhost:8000'
    ]);

    return $this->parseResponse($response);
  }

  private function parseResponse($response): array{
    return [
      'headers' => $response->headers()['Content-Type'],
      'status_code' => $response->status(),
      'body' => json_decode($response->body(), true)
    ];
  }
}
