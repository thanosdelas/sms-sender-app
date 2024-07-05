<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badword extends Model{
  use HasFactory;

  protected $table = 'badwords';

  // TODO: Ensure all badwords are in lowercase before save.
}
