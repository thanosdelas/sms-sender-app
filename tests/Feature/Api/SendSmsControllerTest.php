<?php

namespace Tests\Feature\Api;

use App\Models\User;

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
  public function successfully_sends_sms(): void{
    $this->authenticateUser();

    $data = [
      'message' => 'Sms text message content',
      'to' => '+446999666666',
      'sender_id' => 'Corporation Name'
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(200);
    $this->assertEquals($response->json(), [
      'message' => 'Success',
      'user_id' => $this->user->id
    ]);
  }

  private function authenticateUser(): void{
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
  }
}
