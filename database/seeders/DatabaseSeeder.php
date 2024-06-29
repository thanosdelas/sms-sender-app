<?php

namespace Database\Seeders;

use \Faker\Factory;
use \App\Models\User;
use \App\Models\Message;
use \App\Models\SmsProvider;
use \App\Models\MessageStatus;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder{
  public function __construct(){
    $this->faker = \Faker\Factory::create();
  }

  /**
   * Seed the application's database.
   */
  public function run(): void{
    //
    // Users
    //
    $users = \App\Models\User::factory(10)->create();
    $user_ids = array_map(function($user){
      return $user['id'];
    }, $users->toArray());

    //
    // Fetch first SMS Provider
    //
    $sms_provider = SmsProvider::query()->first();

    //
    // Attach users to the first sms provider.
    //
    foreach ($users as $user) {
      $user->smsProviders()->attach($sms_provider->id);
    }

    //
    // Badwords
    //
    $badwords = [
      'faacebook',
      'instagram',
      'twitter',
      'tiktok'
    ];
    foreach ($badwords as $badword) {
      DB::table('badwords')->insert([
        'badword' => $badword
      ]);
    }

    //
    // Message statuses
    //
    $message_statuses = MessageStatus::all();
    $message_status_ids = array_map(function($message_status){
      return $message_status['id'];
    }, $message_statuses->toArray());

    //
    // Messages
    //
    for ($x = 1; $x <= 300; $x++) {
      DB::table('messages')->insert([
        'message' => $this->faker->sentence,
        'phone_number' => $this->faker->e164PhoneNumber(),
        'user_id' => $user_ids[rand(0, count($user_ids) - 1)],
        'message_status_id' => $message_status_ids[rand(0, count($message_status_ids) - 1)],
      ]);
    }
  }
}
