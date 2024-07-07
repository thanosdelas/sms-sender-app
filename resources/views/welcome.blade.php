<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
  </head>
  <body>
    <h3>{{ $title }}</h3>

    <div>
      Developed and built with:
      <ul>
        <li>
          Ubuntu 22.04.4 LTS (Jammy Jellyfish)
        </li>
        <li>
          PHP 8.1.29 (cli) (built: Jun  6 2024 16:53:25) (NTS)
          Zend Engine v4.1.29, Copyright (c) Zend Technologies
          with Zend OPcache v8.1.29, Copyright (c), by Zend Technologies
        </li>
        <li>
          Laravel Framework 10.48.14
        </li>
        <li>
          PostgreSQL 14.12 (Ubuntu 14.12-0ubuntu0.22.04.1) on x86_64-pc-linux-gnu, compiled by gcc (Ubuntu 11.4.0-1ubuntu1~22.04) 11.4.0, 64-bit
        </li>
        <li>
          Redis 7.2.5
        </li>
      </ul>
    </div>
    <br />

    <div>
      NOTE:
      <pre>
  <!-- -->- The prefered way to test this application is by issuing the command `php artisan test` from a terminal.
  <!-- -->
  <!-- -->- All upstream HTTP requests to the SMS provider are stubbed with response fixtures inside the tests.
  <!-- -->
  <!-- -->- There is an integration api controller test, which covers the entire expectation of sending an SMS message, inside `tests/Fearure/Api/SendSmsControllerTest.php`.
  <!-- -->
  <!-- -->- If you wish to make a real HTTP request to the default SMS provider to actually receive a message to your phone from the tests,
  <!-- -->  you have to put your phone number and enable the following test: `successfully_creates_and_sends_an_sms_message_with_a_real_http_request_to_the_sms_provider`.
  <!-- -->
  <!-- -->- Alternatively put your phone number inside `TestSmsSendController`, and  visit the `test-sms` route below.
  <!-- -->
  <!-- -->- In order to successfully send the SMS request to configure the `APP_SMS_TO_PROVIDER_ACCESS_TOKEN` in the `.env` and `.env.testing` files respectively.
      </pre>
    </div>
    <br />

    <div>
      App Routes:
      <br />
      <br />
      @foreach ($api_routes as $route)
      <div>
        <a href="{{ url('/') }}/{{ $route['path'] }}" target="_blank">{{ url('/') }}/{{ $route['path'] }}</a>
        <pre>{{ $route['description'] }}</pre>
      </div>
      @endforeach
    </div>
  </body>
</html>
