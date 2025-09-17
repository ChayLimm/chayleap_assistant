<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\Flysystem\SymbolicLinkEncountered;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class BotController extends Controller
{
  
  public function index(Request $request){

    $update = $request->all();

    $message = $update['message'];

    $chat = $message['chat'];
    $chatId = $chat['id'];
   
    $text = $message['text'];  

    $gemini = new GeminiController();
    $gemini_response = $gemini->request($text . $chatId);

    $data = json_decode($gemini_response, true);

    // Extract the message
    $message = $data['message'];
   
    Log::info("User message : " . $text . "\n ChatID : $chatId ");
    Log::info($gemini_response);

    $this->botResponse( $chatId,$message);

    $system = new SystemController();
    $system->index($data['function_call']);

  }

  public function setWebhook(){
    $bot_token = env('BOT_TOKEN');
    $url_hook = "https://mustang-tidy-usefully.ngrok-free.app/api/bot";
    $url = "https://api.telegram.org/bot{$bot_token}/setWebhook?url={$url_hook}";

    $resposne = Http::post($url);
    return $resposne;
 }

 public function botResponse($chat_id, $text)
 {
     $bot_token = env('BOT_TOKEN');
     $url = "https://api.telegram.org/bot{$bot_token}/sendMessage"; 
 
     $body = [
         "chat_id" => $chat_id,
         "text" => $text
     ];
 
     $response = Http::post($url, $body);
 
     if ($response->failed()) {
         Log::error('Telegram sendMessage failed', ['response' => $response->body()]);
     }
 
     return $response->json();
 }
 
 
}
