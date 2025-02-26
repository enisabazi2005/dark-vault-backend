<?php

namespace App\Http\Controllers;

use App\Events\NewMessage; // You'll create the event
use App\Jobs\BroadcastNewMessage;
use App\Models\DarkUsers;
use App\Models\Message;
use App\Models\Notification;
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
            $lastMessage = Message::where(function ($query) use ($validated) {
                $query->where('sender_id', $validated['sender_id'])
                    ->where('reciever_id', $validated['reciever_id']);
            })
                ->orWhere(function ($query) use ($validated) {
                    $query->where('sender_id', $validated['reciever_id'])
                        ->where('reciever_id', $validated['sender_id']);
                })
                ->latest('order')
                ->first();

            $order = $lastMessage ? $lastMessage->order + 1 : 1;

            $message = Message::create([
                'sender_id' => $validated['sender_id'],
                'reciever_id' => $validated['reciever_id'],
                'message' => $validated['message'],
                'dark_users_id' => $validated['dark_users_id'],
                'order' => $order,
                'message_sent_at' => now(),
            ]);
            $receiver = DarkUsers::where('request_id', $validated['reciever_id'])->first();
            $sender = DarkUsers::where('request_id', $validated['sender_id'])->first();

            if ($receiver) { 
                Log::info('Receiver Status:', ['offline' => $receiver->offline]);
            
                if ($receiver->offline == true) {
                    try {
                        $notification = Notification::create([
                            'dark_user_id' => $receiver->id,
                            'sender_id' => $sender->id,
                            'message' => $validated['message'],
                        ]);
            
                        Log::info(['Notification created:', $notification]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create notification:', ['error' => $e->getMessage()]);
                        return response()->json(['error' => $e->getMessage()], 500);
                    }
                }
            } else {
                Log::warning('Receiver not found for request_id: ' . $validated['reciever_id']);
            }
            

            $message = Message::find($message->id);

            broadcast(new NewMessage($message));
            // Log::info(['message sent', $message]);
            return response()->json(['message' => 'Message sent successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getMessages($senderRequestId, $receiverRequestId)
    {
        $sender = DarkUsers::where('id', $senderRequestId)->first();
        $receiver = DarkUsers::where('id', $receiverRequestId)->first();

        if (!$sender || !$receiver) {
            return response()->json(['error' => 'One or both users not found'], 404);
        }

        $messages = Message::where(function ($query) use ($sender, $receiver) {
            $query->where('dark_users_id', $sender->id)
                ->where('reciever_id', $receiver->request_id);
        })
            ->orWhere(function ($query) use ($sender, $receiver) {
                $query->where('dark_users_id', $receiver->id)
                    ->where('reciever_id', $sender->request_id);
            })
            ->orderBy('order')
            ->get();

        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found'], 404);
        }
        $responseMessages = $messages->map(function ($message) use ($sender, $receiver) {
            return [
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->reciever_id,
                'message' => $message->message,
                'order' => $message->order,
                'message_sent_at' => $message->message_sent_at,
            ];
        });

        return response()->json($responseMessages);
    }
}
