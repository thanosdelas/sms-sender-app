<?php

namespace App\Services;

use App\Services\Types\MessageToSendStruct;
use App\Services\Types\MessageToSendStructType;
use App\Services\Types\MessageToSendSmsProviderStruct;
use App\Services\Types\MessageToSendSmsProviderStructType;
use App\Services\Types\CreateMessageServiceInterface;

use App\Exceptions\SmsMessageCreateException;
use Illuminate\Support\Facades\Config;

/**
 * Bussiness logic for creating a message_content.
 * TODO:
 *   - Add phone number validation
 *   - Extract here the sms provider per user validation from Message model.
 *   - Prevent creating a new message if the max limit per day is reached.
 */
class CreateMessageService implements CreateMessageServiceInterface{
  private string $message_content;
  private string $sender_id;
  private string $phone_number;
  private string $user_id;
  private array $errors;
  private \App\Models\Message $message;
  private \App\Repositories\BadwordRepository $badwordRepository;

  public function __construct(
    string $message_content,
    string $sender_id,
    string $phone_number,
    string $user_id,
    \App\Repositories\BadwordRepository $badwordRepository
  ){
    $this->message_content = $message_content;
    $this->sender_id = $sender_id;
    $this->phone_number =$phone_number;
    $this->user_id = $user_id;
    $this->badwordRepository = $badwordRepository;

    $this->validateMessage();
  }

  /**
   * Output Data
   */
  public function message(): MessageToSendStructType{
    return new MessageToSendStruct(
      message: $this->message['message'],
      phone_number: $this->message['phone_number'],
      sender_id: $this->message['sender_id'],
      sms_provider_id: $this->message['sms_provider_id'],
      message_status_id: $this->message['message_status_id']
    );
  }

  /**
   * Output Data
   */
  public function smsProvider(): MessageToSendSmsProviderStructType{
    return new MessageToSendSmsProviderStruct(
      provider: $this->message->smsProvider->provider,
      details: $this->message->smsProvider->details,
      api_url: $this->message->smsProvider->api_url,
      sms_max_limit_per_day: $this->message->smsProvider->sms_max_limit_per_day
    );
  }

  /**
   * Create and save a message using the Model.
   */
  public function createMessage(): bool{
    try{
      $this->message = \App\Models\Message::create([
        'message' => $this->message_content,
        'phone_number' => $this->phone_number,
        'sender_id' => $this->sender_id,
        'user_id' => $this->user_id
      ]);

      return true;
    }
    catch (ValidationException $e){
      $this->errors = $e->errors();

      return false;
    }
  }

  public function errors(): array{
    return $this->errors;
  }

  /**
   * Validate and transform/mutate the message to be sent.
   * Remove white space, allow only one space between words,
   * and filter out badwords using the badword repository.
   */
  private function validateMessage(){
    // Remove whitespace and redundant spaces.
    $this->message_content = trim($this->message_content);
    $this->message_content = preg_replace('!\s+!', ' ', $this->message_content);

    $collectCleanWords = [];

    // NOTE: The following assumes that all badwords are single words,
    //       which does not account for phrases of bad words, or bad words
    //       glued with dashes, or with no spaces. In the real word, we should
    //       apply a more complicated solution, which probably uses regular expressions
    //       and/or fuzzy search/approximate string matching.
    $message_content_words = explode(" ", $this->message_content);
    foreach ($message_content_words as $word) {
      // Convert the word to lowercase, as all badwords are stored in lowercase.
      if($this->badwordRepository->isBadWord(strtolower($word)) === false){
        $collectCleanWords[] = $word;
      }
    }

    $this->message_content = implode(" ", $collectCleanWords);

    // throw new SmsMessageCreateException("Provided parameters are invalid. Cannot create message_content.");
  }
}
