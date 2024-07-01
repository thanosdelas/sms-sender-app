<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SendSmsController extends Controller{
  /**
   * Display a listing of the resource.
   */
  public function dispatch(Request $request){
    $messageData = $this->validateMessageParameters($request->json()->all());

    if(array_key_exists('errors', $messageData)){
      return $this->invalidRequest($messageData['errors']);
    }

    $messageData['user_id'] = $request->user()->id;

    try{
      $message = Message::create($messageData);
      $result = $message->save();

      return response()->json($this->responseData($message), 201);
    }
    catch (ValidationException $e){
      return $this->invalidRequest([
        'error' => $e->errors()
      ]);
    }

    return $this->invalidRequest(['error' => 'Could not create product']);
  }

  private function invalidRequest($errors, $httpStatusCode = 422){
    return response()->json(['errors' => $errors], $httpStatusCode);
  }

  private function validateMessageParameters($parameters){
    $messageData = [];

    $validator = Validator::make($parameters, [
      'message' => 'required|string|max:255',
      //
      // TODO: Validate phone number
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

  private function responseData($message){
    return [
      'message' => $message['message'],
      'phone_number' => $message['phone_number'],
      'sender_id' => $message['sender_id'],
      'sms_provider_id' => $message['sms_provider_id'],
      'message_status_id' => $message['message_status_id']
    ];
  }
}
