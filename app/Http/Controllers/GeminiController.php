<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use App\Http\Controllers\BotController;
use Carbon\Carbon;

class GeminiController extends Controller
{

    public function request($prompt){
        $now = Carbon::now();

        $apiKey = env('GEMINI_TOKEN'); 
        $systemPrompt = <<<'EOD'
        You are a reminder assistant. From the user's message, extract the task, time, frequency, and duration, then:
        1. Generate a function call to `set_reminder` (if applicable). use current date if the user doesnt not clarify date (In Cambodia)
        2. Return a JSON response without ``` and with:
           - `function_call` (for the reminder details).
           - `message` (a sweet and friendly reply to the user).
        available function : 
        1 .
        {
        "function_call": {
            "user_id" : "chatId that i sent you"
            "name": "set_reminder",
            "task": "string (e.g., 'order pizza for my lil bro')",
            "time": "string (e.g., 'Asia/phnom penh time' in Laravel timestamp format)",
            "timezone": "string (e.g., 'UTC+07:00,')",
            "frequency": "string (e.g., 'once', 'daily')",
            "description": "string (funny/motivational, e.g., 'Pizza time! Don’t let your lil bro starve!')",
            "status": "string (default: 'pending')"
            
        },
        "message": "string (e.g., 'Alright! I’ll remind you to order pizza at 4 PM. Your bro’s happiness depends on you! 🍕')"
        } 

        2.

        {
        "function_call": {
            "user_id" : "chatId"
            "name": "show_task",
        },
        }      

        3.
        {
        "function_call": {
            "user_id" : "chatId"
            "name": "no_call",
            "description" : "no function is detected"
        },
        "message" : "string (a friendly and sweet message reply to user message)"
        }      

        

        EOD;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $body = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => $systemPrompt . "\n\nUser message and ChatId: " . $prompt . ",if date not provided, use this {$now->toDateTimeString()}"
                        ]

                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'];
     
        return $text;
    }

    public function customRequest($prompt){

        $apiKey = env('GEMINI_TOKEN'); 

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $body = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => $prompt
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'];
        
        return $text;
    }
}
