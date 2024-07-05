<?php

namespace Database\Seeders;

use \Faker\Factory;
use \App\Models\User;
use \App\Models\Message;
use \App\Models\SmsProvider;
use \App\Models\MessageStatus;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDatabaseSeeder extends Seeder{
  public function __construct(){
    $this->faker = \Faker\Factory::create();
  }

  /**
   * Seed the application's database.
   */
  public function run(): void{
    //
    // Fetch SMS Providers
    //
    $sms_provider_sms_to = SmsProvider::where('provider', 'sms.to')->first();
    $sms_provider_acme = SmsProvider::where('provider', 'acme-sms-provider.com')->first();
    $sms_provider_anotherone = SmsProvider::where('provider', 'anotherone-sms-provider.com')->first();

    //
    // Users
    //
    $users = \App\Models\User::factory(10)->create();

    //
    // Attach all users to an sms provider, according to their default selection.
    //
    foreach ($users as $user) {
      $user->smsProviders()->attach($user->defaultSmsProvider);
      $user->smsProviders()->attach($sms_provider_acme);
      $user->smsProviders()->attach($sms_provider_anotherone);
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
  }
}
