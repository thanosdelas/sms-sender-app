<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\UseCases\SendMessageUseCase;
use App\Services\Types\MessageToSendStructType;

use Illuminate\Http\Request;

/**
 * This is to manually test that an SMS can be successfully sent,
 * when we defer the external API request to the SMS provider inside a job.
 */
class TestSmsSendController extends Controller{
  public function test(Request $request){
    $timestamp = date('Y-m-d H:i:s');

    // NOTE: Fill i your phone below to actually receive a message.
    $messageData = [
      'message' => "Sms text message content $timestamp. Visit Facebook to earn money.",
      // 'phone_number' => '',
      'sender_id' => 'CorpSMS'
    ];

    $messageData['user_id'] = User::first()->id;

    $sendMessageUseCase = new SendMessageUseCase(
      message_content: $messageData['message'],
      phone_number: $messageData['/'],
      sender_id: $messageData['sender_id'],
      user_id: $messageData['user_id']
    );

    if($sendMessageUseCase->dispatch() === true){
      return $this->responseSuccess(
        $sendMessageUseCase->messageOutputReloaded(), 201
      );
    }

    if(count($sendMessageUseCase->errors()) > 0){
      // Default is Unprocessable Entity
      $httpStatusCode = 422;
      if(array_key_exists('status_code', $sendMessageUseCase->errors())){
        $httpStatusCode = $sendMessageUseCase->errors()['status_code'];
      }

      return $this->responseError([
        'message' => $sendMessageUseCase->errors()['message']
      ], $httpStatusCode);
    }

    return $this->responseError(['error' => 'Could not create product']);
  }

  private function responseSuccess($data, $httpStatusCode = 200){
    return response()->json(
      $this->responseData($data), $httpStatusCode
    );
  }

  private function responseError($errors, $httpStatusCode = 422){
    return response()->json(
      ['errors' => $errors], $httpStatusCode
    );
  }

  private function responseData(MessageToSendStructType $message): array{
    return [
      'message' => $message->data()['message'],
      'phone_number' => $message->data()['phone_number'],
      'sender_id' => $message->data()['sender_id'],
      'sms_provider_id' => $message->data()['sms_provider_id'],
      'message_status_id' => $message->data()['message_status_id']
    ];
  }
}
