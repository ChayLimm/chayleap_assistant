<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatwootApiController extends Controller
{
    public function index(Request $request){
        Log::info("##################################################################################################################################");
        Log::info(  message: $request->all());
        Log::info("##################################################################################################################################");
    }
}
