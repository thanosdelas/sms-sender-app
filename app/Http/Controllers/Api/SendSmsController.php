<?php

namespace App\Http\Controllers\Api;

use App\UseCases\SendMessageUseCase;
use App\Services\Types\MessageToSendStructType;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SendSmsController extends Controller{
  /**
   * Endpoint controller for `/api/send/sms`
   */
  public function dispatch(Request $request){
    $messageData = $this->validateMessageParameters($request->json()->all());

    if(array_key_exists('errors', $messageData)){
      return $this->responseError($messageData['errors'], 400);
    }

    $messageData['user_id'] = $request->user()->id;

    $sendMessageUseCase = new SendMessageUseCase(
      message_content: $messageData['message'],
      phone_number: $messageData['phone_number'],
      sender_id: $messageData['sender_id'],
      user_id: $messageData['user_id']
    );

    if($sendMessageUseCase->dispatch() === true){
      return $this->responseSuccess(
        $sendMessageUseCase->message(), 201
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

  private function validateMessageParameters($parameters){
    $messageData = [];

    $validator = Validator::make($parameters, [
      'message' => 'required|string|max:255',
      //
      // TODO: Validate phone number here, or defer that to another layer.
      //
      'to' => 'required|string|max:255',
      'sender_id' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
      return ['errors' => $validator->errors()->messages()];
    }

    $messageData = [
      'message' => strip_tags($parameters['message']),
      'phone_number' => strip_tags($parameters['to']),
      'sender_id' => strip_tags($parameters['sender_id'])
    ];

    return $messageData;
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
