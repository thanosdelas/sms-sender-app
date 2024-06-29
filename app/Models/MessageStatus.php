<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model{
  use HasFactory;

  /**
   * Get the message that owns the status.
   */
  public function message(){
    return $this->belongsTo(Message::class);
  }
}
