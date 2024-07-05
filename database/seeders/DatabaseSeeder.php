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
    // Fetch first SMS Provider
    //
    $sms_provider = SmsProvider::query()->first();

    //
    // Users
    //
    $users = \App\Models\User::factory(10)->create();

    //
    // Attach all users to an sms provider, according to their default selection.
    //
    foreach ($users as $user) {
      $user->smsProviders()->attach($user->defaultSmsProvider);
    }

    //
    // Badwords
    //
    $badwords = [
      'facebook',
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
      $user = $users[rand(0, count($users) - 1)];

      DB::table('messages')->insert([
        'message' => $this->faker->sentence, // or paragraph
        'phone_number' => $this->faker->e164PhoneNumber(),
        'sender_id' => "Sent From Corporate Entity User: $user->id",
        'user_id' => $user->id,
        'sms_provider_id' => $user->defaultSmsProvider->id,
        'message_status_id' => $message_status_ids[rand(0, count($message_status_ids) - 1)],
      ]);
    }
  }
}
