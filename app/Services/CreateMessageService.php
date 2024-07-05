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
class CreateMessageService{
  private string $message;
  private string $sender_id;
  private string $phone_number;
  private string $user_id;
  private array $errors;
  private \App\Models\Message $createdMessage;

  public function __construct(
    string $message,
    string $sender_id,
    string $phone_number,
    string $user_id
  ){
    $this->message = $message;
    $this->sender_id = $sender_id;
    $this->phone_number =$phone_number;
    $this->user_id = $user_id;

    $this->validateMessage();
  }

  public function createMessage(): bool{
    try{
      $this->createdMessage = \App\Models\Message::create([
        'message' => $this->message,
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

  public function createdMessage(): array{
    return [
      'message' => $this->createdMessage['message'],
      'phone_number' => $this->createdMessage['phone_number'],
      'sender_id' => $this->createdMessage['sender_id'],
      'sms_provider_id' => $this->createdMessage['sms_provider_id'],
      'message_status_id' => $this->createdMessage['message_status_id']
    ];
  }

  public function errors(): array{
    return $this->errors;
  }

  private function validateMessage(){
    // TODO: Use DI here.
    $badwordRepository = new BadwordRepository();

    // Remove whitespace and redundant spaces.
    $this->message = trim($this->message);
    $this->message = preg_replace('!\s+!', ' ', $this->message);

    $collectCleanWords = [];

    // NOTE: The following assumes that all badwords are single words,
    //       which does not account for phrases of bad words, or bad words
    //       glued with dashes, or with no spaces. In the real word, we should
    //       apply a more complicated solution, which probably uses regular expressions
    //       and/or fuzzy search/approximate string matching.
    $message_words = explode(" ", $this->message);
    foreach ($message_words as $word) {
      // Convert the word to lowercase, as all badwords are stored in lowercase.
      if(!$badwordRepository->isBadWord(strtolower($word))){
        $collectCleanWords[] = $word;
      }
    }

    $this->message = implode(" ", $collectCleanWords);

    // throw new SmsMessageCreateException("Provided parameters are invalid. Cannot create message.");
  }
}
