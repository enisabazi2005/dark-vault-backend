<?php

namespace App\Http\Controllers;

use App\Events\NewMessage; 
use App\Events\UnreadMessagesEvent;
use App\Events\MessageSeenEvent;
use App\Events\TypingIndicator;
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
                if ($receiver->offline == true || $receiver->online || $receiver->away) {
                    try {
                        $notification = Notification::create([
                            'dark_user_id' => $receiver->id,
                            'sender_id' => $sender->id,
                            'sender_name' => $sender->name,
                            'message' => $validated['message'],
                        ]);
        
                        broadcast(new UnreadMessagesEvent([$notification], $receiver->id));
        
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

    public function typingIndicator(Request $request)
{
    $validated = $request->validate([
        'sender_id' => 'required|string|exists:dark_users,request_id',
        'receiver_id' => 'required|string|exists:dark_users,request_id',
        'is_typing' => 'required|boolean'
    ]);

broadcast(new TypingIndicator($validated['sender_id'], $validated['receiver_id'], $validated['is_typing']));
    return response()->json(['status' => 'Typing indicator broadcasted']);
}
public function markAsSeen(Request $request)
{
    $validated = $request->validate([
        'message_id' => 'required|exists:messages,id'
    ]);

    $message = Message::find($validated['message_id']);

    if ($message->seen_at === null) {
        $message->seen_at = now();
        $message->save();

        broadcast(new MessageSeenEvent($message));
    }

    return response()->json(['message' => 'Message marked as seen']);
}
}
