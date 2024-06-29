<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
  public function home(){
    return view('welcome', [
      'title' => 'Laravel Demo App',
      'api_routes' => $this->apiRoutes()
    ]);
  }

  private function apiRoutes(){
    $apiRoutes = [];

    $routes = \Route::getRoutes();
    foreach ($routes as $value) {
      if($value->action['prefix'] === 'api'){
        $apiRoutes[] = $value->uri;
      }
    }

    return $apiRoutes;
  }
}
