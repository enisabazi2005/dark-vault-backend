<?php

namespace App\Http\Controllers;

use App\Events\NewMessage; // You'll create the event
use App\Jobs\BroadcastNewMessage;
use App\Models\DarkUsers;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PusherController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|string|exists:dark_users,request_id',
            'reciever_id' => 'required|string|exists:dark_users,request_id',
            'message' => 'required|string',
            'dark_users_id' => 'required|int|exists:dark_users,id'
        ]);
        
        try {
            $message =  Message::create([
                'sender_id' => $validated['sender_id'],
                'reciever_id' => $validated['reciever_id'],
                'message' => $validated['message'],
                'dark_users_id' => $validated['dark_users_id'],
            ]);
    
            // Broadcast event if using Pusher
            // broadcast(new NewMessage($message))->toOthers();
            // broadcast(new NewMessage($message->message, $message->sender_id, $message->reciever_id));
            // BroadcastNewMessage::dispatch($message->message, $message->sender_id, $message->receiver_id);
            broadcast(new NewMessage($message)); 
            Log::info(['message sent', $message]);
            return response()->json(['message' => 'Message sent successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMessages($senderRequestId, $receiverRequestId)
    {
        // Log the received request parameters for debugging
        Log::info("Received request to get messages", [
            'senderRequestId' => $senderRequestId,
            'receiverRequestId' => $receiverRequestId,
        ]);
    
        // Get the sender's and receiver's dark_users_id by their request_id
        $sender = DarkUsers::where('id', $senderRequestId)->first();
        $receiver = DarkUsers::where('id', $receiverRequestId)->first();

        // Log if users are not found
        if (!$sender) {
            Log::warning("Sender not found", ['senderRequestId' => $senderRequestId]);
        }
        if (!$receiver) {
            Log::warning("Receiver not found", ['receiverRequestId' => $receiverRequestId]);
        }
    
        // If either user is not found, return an error
        if (!$sender || !$receiver) {
            return response()->json(['error' => 'One or both users not found'], 404);
        }
    
        // Log the found users' dark_users_id
        Log::info("Users found", [
            'sender_dark_users_id' => $sender->dark_users_id,
            'receiver_dark_users_id' => $receiver->dark_users_id,
        ]);
    
        // Query the messages table using sender's dark_users_id and receiver's request_id as reciever_id
        $messages = Message::where(function ($query) use ($sender, $receiver) {
            $query->where('dark_users_id', $sender->id) // Use sender's dark_users_id
                  ->where('reciever_id', $receiver->request_id); // Use receiver's dark_users_id
        })
        ->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('dark_users_id', $receiver->id) // Use receiver's dark_users_id
                  ->where('reciever_id', $sender->request_id); // Use sender's dark_users_id
        })
        ->get();
    
        // Log the number of messages found
        Log::info("Number of messages found", ['messages_count' => $messages->count()]);
    
        // Check if messages were found
        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found'], 404);
        }
        $responseMessages = $messages->map(function ($message) use ($sender, $receiver) {
            // If the sender of the message is the senderRequestId, set it accordingly
            if ($message->sender_id == $sender->dark_users_id) {
                return [
                    'sender_id' => $message->sender_id,  // Use actual sender_id from the message
                    'receiver_id' => $message->reciever_id,  // Use actual receiver_id from the message
                    'message' => $message->message,
                ];
            } else {
                // If the sender of the message is the receiverRequestId, reverse the sender/receiver
                return [
                    'sender_id' => $message->sender_id,  // Use actual sender_id from the message
                    'receiver_id' => $message->reciever_id,  // Use actual receiver_id from the message
                    'message' => $message->message,
                ];
            }
        });
    
        // Return the formatted messages as a JSON response
        return response()->json($responseMessages);
    }
    
}
