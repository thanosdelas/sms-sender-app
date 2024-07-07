<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageStatus;
use App\Services\Types\MessageToSendStructType;
use App\Services\Types\MessageToSendSmsProviderStructType;

use Illuminate\Support\Facades\Http;
use App\Exceptions\SmsMessageCreateException;
use Illuminate\Support\Facades\Config;

/**
 * Send an SMS message to the API of the SMS provider.
 *
 * TODO: Add support for throttling, and prevent sending if the
 *       maximum limit amount has been reached per day, according to the
 *       configuration in `sms_provider_user`. Probably cache an sms counter
 *       on Redis for each user, which expires after 24 hours.
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
    // NOTE: The API access_token for each user and SMS provider could
    //       have been stored in the database, but for the sake of this
    //       technical assignment, we load it from the ENV.
    $this->accessToken = env('APP_SMS_TO_PROVIDER_ACCESS_TOKEN');
    $this->smsProviderApiURL = $this->smsProvider->data()['api_url'];

    // Send the message to the SMS provider.
    $response = $this->sendSMSMessage();

    if ($response['status_code'] !== 200 && $response['status_code'] !== 201){
      $this->errors = [
        'status_code' => $response['status_code'],
        'message' => $response['body']['message']
      ];

      return false;
    }

    // NOTE: On success, we should update the message with details
    //       from the SMS provider, as well as update the status.
    $message = Message::find($this->message->data()['message_id']);
    $status_id = MessageStatus::where('status', 'queued_in_sms_provider')->first()->id;
    $message->sms_provider_message_id = $response['body']['message_id'];
    $message->message_status_id = $status_id;
    $message->save();

    $this->responseMessage = $response['body'];
    return true;
  }

  /**
   * Send the message to the SMS provider.
   */
  private function sendSMSMessage(){
    $headers = [
      'Authorization' => "Bearer $this->accessToken",
      'Content-Type' => 'application/json',
    ];

    $response = Http::withHeaders($headers)->post($this->smsProviderApiURL, [
      'message' => $this->message->data()['message'],
      'to' => $this->message->data()['phone_number'],
      'sender_id' => $this->message->data()['sender_id'],
      'bypass_optout' => true, // Should be configured and loaded from the database or from the config.
      'callback_url' => 'localhost:8000' // Should be configured and loaded from the database or from the config.
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
