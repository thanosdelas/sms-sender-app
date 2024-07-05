<?php

namespace Tests\Feature\Api;

use App\Models\User;
use \App\Models\SmsProvider;
use Database\Seeders\TestDatabaseSeeder;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendSmsControllerTest extends TestCase{
  use RefreshDatabase;

  private $user = null;
  private $endpoint = '/api/send/sms';

  /**
   * @test
   */
  public function ensure_endpoint_requires_authentication(){
    $response = $this->postJson($this->endpoint);
    $response->assertStatus(401);

    $json = $response->json();
    $this->assertEquals($json, [
      'message' => 'Unauthenticated.'
    ]);
  }

  /**
   * @test
   */
  public function successfully_creates_an_sms_message(): void{
    $this->seed(TestDatabaseSeeder::class);
    $this->authenticateUser();

    $data = [
      'message' => 'Sms text message content. Visit Facebook to earn money.',
      'to' => '+446999666666',
      'sender_id' => 'Corporation Name'
    ];

    $this->withoutExceptionHandling();
    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201);
    $this->assertEquals($response->json(), [
      'message' => 'Sms text message content. Visit to earn money.',
      'phone_number' => '+446999666666',
      'sender_id' => 'Corporation Name',
      'sms_provider_id' => '100',
      'message_status_id' => '400'
    ]);
  }

  private function authenticateUser(): void{
    $this->user = User::factory()->create();

    $sms_provider_sms_to = SmsProvider::where('provider', 'sms.to')->first();
    $this->user->smsProviders()->attach($this->user->defaultSmsProvider);

    $this->actingAs($this->user);
  }
}
