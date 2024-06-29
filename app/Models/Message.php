<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model{
  use HasFactory;

  /**
   * Get the status associated with the message.
   */
  public function status(){
    return $this->hasOne(MessageStatus::class);
  }
}
