<?php

namespace Tests\Unit\Models;

use App\Models\SmsProvider;
use App\Models\User;
use App\Models\Message;
use App\Models\MessageStatus;
use Database\Seeders\TestDatabaseSeeder;
use App\Exceptions\UserSmsProviderException;
use App\Exceptions\ImmutableMessageAttributesException;

use Tests\TestCase;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * NOTE: For more generic and massive testing use:
 *       `$this->seed(TestDatabaseSeeder::class);`
 *       to successfully setup and multiple entities.
 */
class MessageTest extends TestCase{
  use RefreshDatabase;

  /**
   * @test
   */
  public function successfully_creates_a_message(): void{
    $sms_provider_sms_to = SmsProvider::where('provider', 'sms.to')->first();

    $user_first = User::factory()->create([
      'name' => 'First User',
      'email' => 'first_user@example.com'
    ]);
    $user_first->smsProviders()->attach($user_first->defaultSmsProvider);

    $user_second = User::factory()->create([
      'name' => 'Second User',
      'email' => 'second_user@example.com'
    ]);
    $user_second->smsProviders()->attach($user_second->defaultSmsProvider);

    $messageData = [
      'message' => 'This is an SMS message',
      'phone_number' => "+306990999999",
      'sender_id' => "Corporation Sender",
      'user_id' => $user_second->id
    ];

    // Create message
    $message = Message::create($messageData);

    $this->assertModelExists($message);

    $this->assertDatabaseHas('messages', [
      'id' => $message->id,
      'message' => $message->message,
      'phone_number' => $message->phone_number,
      'sender_id' => "Corporation Sender",
      'user_id' => $user_second->id,
      'sms_provider_id' => $sms_provider_sms_to->id,
      'message_status_id' => '500'
    ]);

    $this->assertEquals($message->messageStatus->status, 'created');
    $this->assertEquals($message->smsProvider->provider, 'sms.to');
  }

  /**
   * @test
   */
  public function does_not_create_a_message_when_the_user_is_not_attached_to_any_sms_providers(): void{
    $user = User::factory()->create();

    $this->expectException(UserSmsProviderException::class);
    $this->expectExceptionMessage('There are no sms providers configured for the provided user. Message cannot be created.');

    // Create message
    $message = Message::factory()->forUser($user)->create();
  }

  /**
   * @test
   */
  public function does_not_create_a_message_when_the_default_user_sms_provider_is_not_in_configured_user_sms_providers(): void{
    $sms_provider_acme = SmsProvider::where('provider', 'acme-sms-provider.com')->first();
    $user = User::factory()->create();
    $user->smsProviders()->attach($sms_provider_acme);

    $this->expectException(UserSmsProviderException::class);
    $this->expectExceptionMessage('The default sms provider for this user is not configured within the sms providers. Message cannot be created.');

    // Create message
    $message = Message::factory()->forUser($user)->create();
  }

  /**
   * @test
   */
  public function restricts_specific_message_attributes_from_beeing_updated(): void{
    $sms_provider_sms_to = SmsProvider::where('provider', 'sms.to')->first();

    $user = User::factory()->create();
    $user->smsProviders()->attach($user->defaultSmsProvider);

    // Create message
    $message = Message::factory()->create();

    $this->assertEquals($message->messageStatus->status, 'created');
    $this->assertEquals($message->smsProvider->provider, 'sms.to');

    // Update message with restricted attributes and expect to throw error.
    $this->expectException(ImmutableMessageAttributesException::class);
    $this->expectExceptionMessage('Update is not allowing for any of the following attributes: [message, sender_id, phone_number, sms_provider_id, user_id]');
    $message->message = 'Updated message content';
    $message->sender_id = 'Updated sender_id';
    $message->phone_number = 'Updated phone_number';
    $message->sms_provider_id = 'Updated sms_provider_id';
    $message->user_id = 'Updated user_id';
    $message->save();
  }
}
