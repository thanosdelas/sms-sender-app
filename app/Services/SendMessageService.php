<?php

namespace App\Services;

use App\Repositories\BadwordRepository;

use App\Exceptions\SmsMessageCreateException;
use Illuminate\Support\Facades\Config;

/**
 * Bussiness logic for creating a message.
 * TODO:
 *   - Add phone number validation
 *   - Extract here the sms provider per user validation from Message model.
 */
class SendMessageService{
  private App\Services\CreateMessageService $message;
  private array $errors;

  public function __construct(
    App\Services\CreateMessageService $message
  ){
    $this->message = $message;
  }

  public function send(): bool{
    try{
      $this->createdMessage = \App\Models\Message::create([
        'message' => $this->message,
        'phone_number' => $this->phone_number,
        'sender_id' => $this->sender_id,
        'user_id' => $this->user_id
      ]);

      $this->sendSMSMessage();

      return true;
    }
    catch (ValidationException $e){
      $this->errors = $e->errors();

      return false;
    }
  }

  private function sendSMSMessage(){
    // $url = 'https://api.sms.to/sms/send';
    // $url = 'https://example.com';
    // $url = "https://dummyjson.com/posts/add";
    $url = "localhost:4444";

    $data = [
      "message" => "This is test and \n this is a new line",
      "to" => "+35799999999999",
      "bypass_optout" => true,
      "sender_id" => "SMSto",
      "callback_url" => "localhost:8000"
    ];

    // $headers = [
    //   'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2F1dGg6ODA4MC9hcGkvdjEvdXNlcnMvYXBpL2tleXMvZ2VuZXJhdGUiLCJpYXQiOjE3MjAxNjU1MTUsIm5iZiI6MTcyMDE2NTUxNSwianRpIjoiYW1SSE9mUUNCbUwxaGV0QSIsInN1YiI6NDU5OTQ5LCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.9iJtSaUtUXz4MAAsdDMNHRSOp4a2bxosi1VkRGQDn2E',
    //   'Content-Type: application/json'
    // ];

    $headers = [
      'Authorization: Bearer INVALID',
      'Content-Type: application/json'
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    $responseInfo = curl_getinfo($ch);

    var_dump($responseInfo['content_type']);
    var_dump($responseInfo['http_code']);
    var_dump(curl_errno($ch));
    // exit();

    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
      var_dump($ch);
      exit();

    } else {
      echo "\n\n\n\nNOT ERROR";
      echo 'Response:' . $response;

      var_dump($response);
      exit();
    }

    curl_close($ch);
  }
}
