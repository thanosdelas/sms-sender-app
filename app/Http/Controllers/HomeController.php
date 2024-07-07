<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller{
  public function home(){
    return view('welcome', [
      'title' => 'Sms Sender App',
      'api_routes' => $this->apiRoutes()
    ]);
  }

  private function apiRoutes(){
    $apiRoutes = [];

    $apiRoutes[] = [
      'path' => 'test-sms',
      'description' => 'Use to manually test sending an SMS to the provider and dispatch it the user phone. Requires configuration.'
    ];

    $apiRoutes[] = [
      'path' => 'api/send/sms',
      'description' => 'REST API endpoint to send an SMS to the provider. Requires authentication. Supports only POST requests.'
    ];

    return $apiRoutes;
  }
}
