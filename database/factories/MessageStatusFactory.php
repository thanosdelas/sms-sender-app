<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MessageStatus>
 */
class MessageStatusFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array{
    return [
      'id' => '100',
      'status' => "delivered",
      'description' => "Message was successfully delivered."
    ];
  }
}
