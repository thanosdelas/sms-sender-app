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

    // print_r($routes);
    // exit();

    foreach ($routes as $value) {
      // if($value->action['prefix'] === 'api'){
      //   $apiRoutes[] = $value->uri;
      // }

      if (in_array('api', $route->action['middleware'] ?? [])) {
        $apiRoutes[] = [
            'uri' => $route->uri,
            'name' => $route->getName(),
            'methods' => $route->methods,
            'action' => $route->getActionName(),
        ];
      }
    }

    return $apiRoutes;
  }
}
