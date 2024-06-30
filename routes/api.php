<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SendSmsController;

Route::group(['middleware' => 'auth:sanctum'], function () {
  Route::get('/user', function (Request $request) {
    return $request->user();
  });

  Route::post('/send/sms', [SendSmsController::class, 'dispatch']);
});
