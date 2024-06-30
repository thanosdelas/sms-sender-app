<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model{
  use HasFactory;

  // public static function boot(){
  //   parent::boot();

  //   static::creating(function ($model) {
  //     $model->validate();
  //   });
  // }

  public function status(){
    return $this->hasOne(MessageStatus::class);
  }

  public function user(){
    return $this->belongsTo(User::class);
  }

  public function smsProvider(){
    return $this->belongsTo(SmsProvider::class);
  }

  public function validate(){
    // $messages = [
    //   'title.unique' => 'A product with this title already exists.',
    // ];

    $validator = Validator::make($this->attributes, [
      'message' => 'required|string|max:255',
      'phone_number' => 'required|string|max:255',
      'user_id' => 'required',
      'sms_provider_id' => 'required',
      'message_status_id' => 'required'
    ], $messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }
  }

}
