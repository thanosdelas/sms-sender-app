<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\MessageStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array{
    return [
      'message' => $this->faker->sentence,
      'phone_number' => $this->faker->e164PhoneNumber(),
      'sender_id' => 'Corporation Sender',
      'user_id' => User::query()->first(),
      'sms_provider_id' => User::query()->first()->defaultSmsProvider,
      'message_status_id' => MessageStatus::where('status', 'delivered')->first(),
    ];
  }

  public function forUser(User $user){
    return $this->state([
      'user_id' => $user->id,
    ]);
  }
}
