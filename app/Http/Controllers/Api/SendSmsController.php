<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SendSmsController extends Controller{
  /**
   * Display a listing of the resource.
   */
  public function dispatch(Request $request){
    return response()->json([
      'message' => 'Success',
      'user_id' => $request->user()->id
    ], 200);
  }
}
