<?php

namespace App\Models;

use App\Exceptions\UserSmsProviderException;
use App\Exceptions\ImmutableMessageAttributesException;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model{
  use HasFactory;

  protected $fillable = [
    'message',
    'phone_number',
    'sender_id',
    'user_id'
  ];

  public static function boot(){
    parent::boot();

    static::creating(function ($model) {
      $model->validateProvidedParameters();
      $model->validateSmsProviderConfiguration();

      // Default state, when creating a message.
      // Ensure `sms_provider_id` and `message_status_id` have always these
      // values on message create, regardless of any outside assignment.
      $model->sms_provider_id = $model->user->defaultSmsProvider->toArray()['id'];
      $model->message_status_id = 500;
    });

    // After a message has been created, we should consider some of the fields/attributes immutable.
    // Specifically, we should prevent update on:
    //  - The `user_id`, because a message cannot change ownership.
    //  - The `message`, `sender_id`, `phone_number`, `sms_provider_id`, if it has already been sent to the SMS provider (check status).
    //  - [TODO] The `sms_provider_message_id`, when it has been received once and references the external id on the SMS provider (dependes on status).
    static::updating(function ($model) {
      $immutableAttributes = [
        'message',
        'sender_id',
        'phone_number',
        'sms_provider_id',
        'user_id'
      ];

      $changedAttributes = array_filter($immutableAttributes, function($attribute) use ($model){
        return $model->isDirty($attribute);
      });

      if(count($changedAttributes) > 0){
        $changedAttributesString = implode(", ", $changedAttributes);

        throw new ImmutableMessageAttributesException("Update is not allowing for any of the following attributes: [$changedAttributesString]");
      }
    });
  }

  public function messageStatus(){
    return $this->belongsTo(MessageStatus::class);
  }

  public function user(){
    return $this->belongsTo(User::class);
  }

  public function users(){
    return $this->hasMany(User::class);
  }

  public function smsProvider(){
    return $this->belongsTo(SmsProvider::class);
  }

  private function validateProvidedParameters(){
    $validator = Validator::make($this->attributes, [
      'message' => 'required|string|max:255',
      'phone_number' => 'required|string|max:255',
      'sender_id' => 'required|string|max:255',
      'user_id' => 'required'
    ]);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }
  }

  private function validateSmsProviderConfiguration(){
    $user_sms_providers = $this->user->smsProviders->toArray();

    if(count($user_sms_providers) === 0){
      $errorMessage = "There are no sms providers configured for the provided user. Message cannot be created.";

      throw new UserSmsProviderException($errorMessage);
    }

    // Ensure default user sms provider is inside the
    // configured sms providers (`sms_provider_user` pivot table).
    // We can also extract specific rate configuration for current user and sms provider (if exists).
    if(count($user_sms_providers) > 0){
      $defaultSmsProviderId = $this->user->defaultSmsProvider->id;
      $user_sms_provider = array_filter($user_sms_providers,
        function($sms_provider) use ($defaultSmsProviderId){
          return $sms_provider['id'] === $defaultSmsProviderId;
      });

      if(count($user_sms_provider) !== 1){
        $errorMessage = "The default sms provider for this user is not configured within the sms providers. Message cannot be created.";

        throw new UserSmsProviderException($errorMessage);
      }
    }
  }
}
