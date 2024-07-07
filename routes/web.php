<?php

use Illuminate\Support\Facades\Route;

use App\Jobs\SendSMSMessageToProviderJob;
use App\Services\Types\MessageToSendStruct;
use App\Services\Types\MessageToSendSmsProviderStruct;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestSmsSendController;

Route::get('/', [HomeController::class, 'home']);

// Used for manual testing the job queue.
Route::get('/test-sms', [TestSmsSendController::class, 'test']);
