<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Reminder;
use Carbon\Carbon;


class SystemController extends Controller
{
    public function index($function_call)
    {

        switch ($function_call['name']) {  // Null coalescing to handle null case
            case 'set_reminder':
                Log::info("Calling set reminder function", ['data' => $function_call]);
                $this->set_reminder($function_call); // Ensure arguments exist
                return 'Creating a new reminder...';
            
            case 'show_task':
                Log::info("Calling show task function", ['data' => $function_call]);
                $this->show_task($function_call); // Ensure arguments exist
                return 'Fetching tasks...';
            
            case 'no_call': 
                Log::warning("Function call is null or invalid");
                return 'No action specified.';
            
            default:
                Log::error("Unknown function called", ['name' => $function_call['name']]);
                return 'Invalid action.';
        }

    }

    public function set_reminder($data) // Renamed parameter for clarity
    {
        try {
            $reminder = Reminder::create([
                'user_id' => $data['user_id'],
                'task' => $data['task'],
                'reminder_date' => $data['time'],
                'timezone' => $data['timezone'],
                'frequency' => $data['frequency'],
                'description' => $data['description'],
                'status' => $data['status'] ?? 'pending',
            ]);

            return response()->json([
                'message' => 'Reminder set successfully!',
                'reminder' => $reminder,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create reminder: " . $e->getMessage());
            return response()->json(['error' => 'Reminder creation failed'], 500);
        }
    }


    public function show_task($data) {
       

        $user_tasks = Reminder::where('user_id', $data['user_id'])->get();
        
        if ($user_tasks->isEmpty()) {
            $bot = new BotController();
            $bot->botResponse($data['user_id'], 'No tasks found for this user');
        }
    
        $prompt = "Turn this into human readable with clean format just task and due date so i can send it to telegram chat:\n";
        $prompt .= $user_tasks->toJson(); 
        
        try {
            $gemini = new GeminiController();
            $response = $gemini->customRequest($prompt);
            
            $bot = new BotController();
            $bot->botResponse($data['user_id'], $response);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Log error and return meaningful response
            \Log::error("Failed to process tasks: " . $e->getMessage());
            return response()->json(['error' => 'Failed to process tasks'], 500);
        }
    }

       // New polling function to check and trigger reminders
       public function checkReminders()
       {
           try {
               $now = Carbon::now();
               Log::info("Checking reminders at: " . $now->toDateTimeString());
               
               // Get all pending reminders where reminder_date is <= current time
               $dueReminders = Reminder::where('status', 'pending')
                   ->where('reminder_date', '<=', $now->toDateTimeString())
                   ->get();
               
               if ($dueReminders->isEmpty()) {
                   Log::info("No pending reminders found to trigger");
                   return;
               }
               
               foreach ($dueReminders as $reminder) {
                   try {
                       $bot = new BotController();
                       $message = "⏰ Reminder: {$reminder->task}\n{$reminder->description}";
                       $bot->botResponse($reminder->user_id, $message);
                       
                       // Update status based on frequency
                       if ($reminder->frequency === 'once') {
                           $reminder->update(['status' => 'completed']);
                       } else {
                           // For recurring tasks, you might want to update the reminder_date here
                           // based on the frequency (daily, weekly, etc.)
                           // This is a placeholder for that logic
                           $reminder->update(['reminder_date' => $this->calculateNextReminder($reminder)]);
                       }
                       
                       Log::info("Reminder triggered successfully", ['reminder_id' => $reminder->id]);
                   } catch (\Exception $e) {
                       Log::error("Failed to trigger reminder {$reminder->id}: " . $e->getMessage());
                   }
               }
               
               return response()->json(['success' => true, 'triggered_count' => $dueReminders->count()]);
               
           } catch (\Exception $e) {
               Log::error("Failed to check reminders: " . $e->getMessage());
               return response()->json(['error' => 'Failed to check reminders'], 500);
           }
       }
       
       // Helper function to calculate next reminder date for recurring tasks
       protected function calculateNextReminder($reminder)
       {
           $currentDate = Carbon::parse($reminder->reminder_date);
           
           switch ($reminder->frequency) {
               case 'daily':
                   return $currentDate->addDay();
               case 'weekly':
                   return $currentDate->addWeek();
               case 'monthly':
                   return $currentDate->addMonth();
               default:
                   return $currentDate;
           }
       }


}
