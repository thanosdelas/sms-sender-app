<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Message;
use App\Models\MessageStatus;
use Database\Seeders\TestDatabaseSeeder;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * NOTE: For these tests we are heavily relying on the
 *       the test database seeder, to successfully setup
 *       and populate all the required entities.
 */
class MessageTest extends TestCase{
  use RefreshDatabase;

  /**
   * @test
   * Create a message.
   */
  public function successfully_creates_a_message(): void{
    $this->seed(TestDatabaseSeeder::class);

    $messageData = [
      'title' => 'This is an SMS message',
      'phone_number' => "+306990999999"
    ];

    $message = Message::factory()->create();

    // Ensure the user is attached to at least one sms provider,
    // and that the default sms provider exists inside the pivot table.
    $user_sms_providers = $message->user->smsProviders;
    $defaultSmsProviderId = $message->user->defaultSmsProvider->id;
    $user_sms_provider = array_filter($user_sms_providers->toArray(),
      function($sms_provider) use ($defaultSmsProviderId){
        return $sms_provider['id'] === $defaultSmsProviderId;
    });

    $this->assertEquals(count($user_sms_provider), 1);
    $this->assertEquals($user_sms_provider[0]['provider'], 'sms.to');

    $this->assertModelExists($message);

    $this->assertDatabaseHas('messages', [
      'id' => $message->id,
      'message' => $message->message,
      'phone_number' => $message->phone_number,
      'sms_provider_id' => $user_sms_provider[0]['id']
    ]);
  }

  /**
   * @test
   * Create a message.
   */
  public function does_not_create_a_message_when_the_user_is_not_attached_to_sms_providers(): void{
    $user = User::factory()->create();

    // $messageData = [
    //   'title' => 'This is an SMS message',
    //   'phone_number' => "+306990999999"
    // ];

    $message = Message::factory()->forUser($user)->create();

    // Ensure the user is attached to at least one sms provider,
    // and that the default sms provider exists inside the pivot table.
    $user_sms_providers = $message->user->smsProviders;
    $defaultSmsProviderId = $message->user->defaultSmsProvider->id;
    $user_sms_provider = array_filter($user_sms_providers->toArray(),
      function($sms_provider) use ($defaultSmsProviderId){
        return $sms_provider['id'] === $defaultSmsProviderId;
    });

    $this->assertEquals(count($user_sms_provider), 0);
  }
}
