<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\ChatwootApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/chayyim-assistance",[GeminiController::class,'request']);
Route::post("/setWebhook",[BotController::class,'setWebhook']);

Route::post("/bot",[BotController::class,'index']);

Route::post('/setWebhook',action: [ChatwootApiController::class, 'index']);