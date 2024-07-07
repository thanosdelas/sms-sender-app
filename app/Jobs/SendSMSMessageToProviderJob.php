<?php

namespace App\Jobs;

use App\Services\SendMessageService;
use App\Services\Types\MessageToSendStructType;
use App\Services\Types\MessageToSendSmsProviderStructType;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSMSMessageToProviderJob implements ShouldQueue{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private MessageToSendStructType $message;
  private MessageToSendSmsProviderStructType $smsProvider;

  /**
   * Create a new job instance.
   */
  public function __construct(
    MessageToSendStructType $message,
    MessageToSendSmsProviderStructType $smsProvider
  ){
    $this->message = $message;
    $this->smsProvider = $smsProvider;
  }

  /**
   * Execute the job.
   */
  public function handle(): void{
    $sendMessageService = new SendMessageService($this->message, $this->smsProvider);

    // TODO: Handle errors not thrown from exceptions and log them.
    $sendMessageService->send();
  }
}
