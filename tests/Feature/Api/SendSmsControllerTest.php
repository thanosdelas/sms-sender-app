<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Message;
use App\Models\SmsProvider;
use App\Services\SendMessageService;
use Database\Seeders\TestDatabaseSeeder;
use App\Services\Types\MessageToSendStruct;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
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
  public function fails_to_create_and_sends_an_sms_message_because_parameters_are_smissing(): void{
    $this->seed(TestDatabaseSeeder::class);
    $this->authenticateUser();
    $beforeMessagesCount = Message::count();

    $expectedResponse = '{"message":"Message is queued for sending! Please check report for update","success":true,"message_id":"459949-1720166-22b8-0cf1-8214b1ca-d9"}';
    $expectedResponseParsed = json_decode($expectedResponse, true);

    // Stub the HTTP request to the SMS provider.
    Http::fake([
      'https://api.sms.to/sms/send' => Http::response($expectedResponseParsed, 200)
    ]);

    $data = [
      'message' => '',
      'to' => '',
      'sender_id' => ''
    ];

    $this->withoutExceptionHandling();
    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(400);
    $this->assertEquals($response->json(), [
      "errors" => [
          "message" => [
          "The message field is required."
        ],
        "to" => [
          "The to field is required."
        ],
        "sender_id" => [
          "The sender id field is required."
        ]
      ]
    ]);

    // Ensure no message has been created in database.
    $afterMessagesCount = Message::count();
    $this->assertEquals($afterMessagesCount, $beforeMessagesCount);
  }

  /**
   * @test
   */
  public function successfully_creates_and_sends_an_sms_message_by_stubbing_the_upstream_http_request_but_response_contains_errors(): void{
    // $this->markTestSkipped('Temporarilly skipped');

    $this->seed(TestDatabaseSeeder::class);
    $this->authenticateUser();
    $beforeMessagesCount = Message::count();

    // Stub fixture
    $expectedResponse = '{"success":false,"message":"Invalid API Key or Token","data":[]}';
    $expectedResponseParsed = json_decode($expectedResponse, true);

    // Stub the HTTP request to the SMS provider.
    Http::fake([
      'https://api.sms.to/sms/send' => Http::response($expectedResponseParsed, 401)
    ]);

    $data = [
      'message' => 'Sms text message content. Visit Facebook to earn money.',
      'to' => '+446999666666',
      'sender_id' => 'CorpSMS'
    ];

    $this->withoutExceptionHandling();
    $response = $this->postJson($this->endpoint, $data);

    // Status code is propagated from the external api request.
    $response->assertStatus(401);

    $this->assertEquals($response->json(),[
      "errors" => [
        "message" => "Invalid API Key or Token"
      ]
    ]);

    // Ensure the message has been saved in database.
    // TODO: Match the entry on the database, once the id of the created message is returned in the reponse
    $afterMessagesCount = Message::count();
    $this->assertEquals($afterMessagesCount, $beforeMessagesCount + 1);
  }

  /**
   * @test
   */
  public function successfully_creates_and_sends_an_sms_message_by_stubbing_the_upstream_http_request_but_response_contains_errors_bad_request(): void{
    // $this->markTestSkipped('Temporarilly skipped');

    $this->seed(TestDatabaseSeeder::class);
    $this->authenticateUser();
    $beforeMessagesCount = Message::count();

    // Stub fixture
    $expectedResponse = '{"message":"The alphanumeric Sender ID cannot contain more than 11 characters."}';
    $expectedResponseParsed = json_decode($expectedResponse, true);

    // Stub the HTTP request to the SMS provider.
    Http::fake([
      'https://api.sms.to/sms/send' => Http::response($expectedResponseParsed, 400)
    ]);

    $data = [
      'message' => 'Sms text message content. Visit Facebook to earn money.',
      'to' => '+446999666666',
      'sender_id' => 'CorpSMS'
    ];

    $this->withoutExceptionHandling();
    $response = $this->postJson($this->endpoint, $data);

    // TODO: Maybe 400, 422, 502, 503, 504, according to upstream request error.
    $response->assertStatus(400);

    $this->assertEquals($response->json(),[
      "errors" => [
        "message" => "The alphanumeric Sender ID cannot contain more than 11 characters."
      ]
    ]);

    // Ensure the message has been saved in database.
    // TODO: Match the entry on the database, once the id of the created message is returned in the reponse
    $afterMessagesCount = Message::count();
    $this->assertEquals($afterMessagesCount, $beforeMessagesCount + 1);
  }

  /**
   * @test
   */
  public function successfully_creates_and_sends_an_sms_message_by_stubbing_the_upstream_http_request(): void{
    $this->seed(TestDatabaseSeeder::class);
    $this->authenticateUser();
    $beforeMessagesCount = Message::count();

    // Stub fixture
    $expectedResponse = '{"message":"Message is queued for sending! Please check report for update","success":true,"message_id":"459949-1720166-22b8-0cf1-8214b1ca-d9"}';
    $expectedResponseParsed = json_decode($expectedResponse, true);

    // Stub the HTTP request to the SMS provider.
    Http::fake([
      'https://api.sms.to/sms/send' => Http::response($expectedResponseParsed, 200)
    ]);

    $data = [
      'message' => 'Sms text message content. Visit Facebook to earn money.',
      'to' => '+446999666666',
      'sender_id' => 'CorpSMS'
    ];

    $this->withoutExceptionHandling();
    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201);
    $this->assertEquals($response->json(), [
      'message' => 'Sms text message content. Visit to earn money.',
      'phone_number' => '+446999666666',
      'sender_id' => 'CorpSMS',
      'sms_provider_id' => '100',
      'message_status_id' => '250'
    ]);

    // Ensure the message has been saved in database.
    // TODO: Match the entry on the database, once the id of the created message is returned in the reponse
    $afterMessagesCount = Message::count();
    $this->assertEquals($afterMessagesCount, $beforeMessagesCount + 1);
  }

  /**
   * @test
   * WARNING: The following test is going to make a real HTTP request
   *          to the SMS provider. If everything is configured properly,
   *          set your phone number in the `$phoneNummber` variable below, to receive a message.
   */
  public function successfully_creates_and_sends_an_sms_message_with_a_real_http_request_to_the_sms_provider(): void{
    //
    // This test is skipped, as the previous tests, with stubbed upstream requests are sufficient for development.
    //
    $this->markTestSkipped('[Skipped]. Enable it if you wish to send a real HTTP request to the SMS provider.');
    //
    // Set your phone number here, to actually receive a message to your phone.
    //
    $phoneNummber = '';

    $this->seed(TestDatabaseSeeder::class);
    $this->authenticateUser();
    $beforeMessagesCount = Message::count();

    $data = [
      'message' => 'Sms text message content. Visit Facebook to earn money.',
      'to' => $phoneNummber,
      'sender_id' => 'CorpSMS'
    ];

    $this->withoutExceptionHandling();
    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201);
    $this->assertEquals($response->json(), [
      'message' => 'Sms text message content. Visit to earn money.',
      'phone_number' => $phoneNummber,
      'sender_id' => 'CorpSMS',
      'sms_provider_id' => '100',
      'message_status_id' => '250'
    ]);

    // Ensure the message has been saved in database.
    // TODO: Match the entry on the database, once the id of the created message is returned in the reponse
    $afterMessagesCount = Message::count();
    $this->assertEquals($afterMessagesCount, $beforeMessagesCount + 1);
  }

  private function authenticateUser(): void{
    $this->user = User::factory()->create();

    $sms_provider_sms_to = SmsProvider::where('provider', 'sms.to')->first();
    $this->user->smsProviders()->attach($this->user->defaultSmsProvider);

    $this->actingAs($this->user);
  }
}
