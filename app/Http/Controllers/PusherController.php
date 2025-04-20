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
use App\Models\MessageReactions;
use App\Events\MessageReacted;
use Illuminate\Support\Facades\Auth;


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
                'seen_at' => null,
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
                            'message_id' => $message->id
                        ]);

                        $validNotifications = Notification::where('dark_user_id', $receiver->id)
                        ->whereHas('message') 
                        ->get();

                        // broadcast(new UnreadMessagesEvent([$notification], $receiver->id));
                        broadcast(new UnreadMessagesEvent($validNotifications, $receiver->id));


                        Log::info(['Notification created:', $notification]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create notification:', ['error' => $e->getMessage()]);
                        return response()->json(['error' => $e->getMessage()], 500);
                    }
                }
            } else {
                Log::warning('Receiver not found for request_id: ' . $validated['reciever_id']);
            }

            $message = Message::with('reactions')->find($message->id);


            broadcast(new NewMessage($message));
            return response()->json([
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'reciever_id' => $message->reciever_id,
                    'message' => $message->message,
                    'dark_users_id' => $message->dark_users_id,
                    'order' => $message->order,
                    'message_sent_at' => $message->message_sent_at,
                    'seen_at' => $message->seen_at,
                    'reactions' => $message->reactions, 
                ],
            ], 201);
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

        // $messages = Message::where(function ($query) use ($sender, $receiver) {
        //     $query->where('dark_users_id', $sender->id)
        //         ->where('reciever_id', $receiver->request_id);
        // })
        //     ->orWhere(function ($query) use ($sender, $receiver) {
        //         $query->where('dark_users_id', $receiver->id)
        //             ->where('reciever_id', $sender->request_id);
        //     })
        //     ->orderBy('order')
        //     ->get();
        $messages = Message::with('reactions') // <-- this part
            ->where(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $sender->request_id)
                    ->where('reciever_id', $receiver->request_id);
            })
            ->orWhere(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $receiver->request_id)
                    ->where('reciever_id', $sender->request_id);
            })
            ->orderBy('order')
            ->get();


        if ($messages->isEmpty()) {
            return response()->json(['message' => 'No messages found'], 404);
        }
        $responseMessages = $messages->map(function ($message) use ($sender, $receiver) {
            return [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->reciever_id,
                'message' => $message->message,
                'order' => $message->order,
                'message_sent_at' => $message->message_sent_at,
                'seen_at' => $message->seen_at,
                'reactions' => $message->reactions->map(function ($reaction) {
                    return [
                        'reaction_type' => $reaction->reaction_type,
                        'reacted_by' => $reaction->reacted_by,
                    ];
                }),
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
            'sender_id' => 'required|exists:dark_users,request_id',
            'receiver_id' => 'required|exists:dark_users,request_id',
        ]);

        // Find the latest message between the sender and receiver
        $message = Message::where('sender_id', $validated['sender_id'])
            ->where('reciever_id', $validated['receiver_id'])
            ->latest('created_at') // Get the most recent message
            ->first();

        if ($message && $message->seen_at === null) {
            $message->seen_at = now();
            $message->save();

            broadcast(new MessageSeenEvent($message));
        }

        return response()->json(['message' => 'Message marked as seen']);
    }

    public function getMessageStatus(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|exists:dark_users,request_id',
            'receiver_id' => 'required|exists:dark_users,request_id',
        ]);

        // Fetch the latest message between sender and receiver
        $message = Message::where(function ($query) use ($validated) {
            $query->where('sender_id', $validated['sender_id'])
                ->where('reciever_id', $validated['receiver_id']);
        })
            ->orWhere(function ($query) use ($validated) {
                $query->where('sender_id', $validated['receiver_id'])
                    ->where('reciever_id', $validated['sender_id']);
            })
            ->latest('created_at')
            ->first();

        if ($message) {
            // ✅ Fire event to update real-time seen status
            broadcast(new MessageSeenEvent($message));
        }

        return response()->json([
            'message_id' => $message?->id,
            'seen_at' => $message?->seen_at,
        ]);
    }

    public function react(Request $request, $messageId)
    {
        $validated = $request->validate([
            'dark_users_id' => 'required|exists:dark_users,id',
            'reaction_type' => 'required|in:heart,laugh,curious,like,dislike,cry',
        ]);

        $reaction = MessageReactions::updateOrCreate(
            [
                'message_id' => $messageId,
                'reacted_by' => $validated['dark_users_id'],
            ],
            [
                'reaction_type' => $validated['reaction_type'],
            ]
        );

        // Optionally fetch updated message + reactions to broadcast
        $message = Message::with('reactions')->find($messageId);

        broadcast(new MessageReacted($message))->toOthers();

        return response()->json(['message' => 'Reaction updated', 'reaction' => $reaction]);
    }
    public function getReactions($messageId)
    {
        $reactions = MessageReactions::where('message_id', $messageId)
            ->with('reactedByUser') // if you want more info about user who reacted
            ->get();

        return response()->json([
            'message_id' => $messageId,
            'reactions' => $reactions
        ]);
    }
    public function deleteReaction(Request $request, $messageId)
    {
        // Get the current authenticated user's ID
        $user = Auth::user();
        // dd($user);
        $userId = $user->id;

        // Find the reaction for this message and user
        $reaction = MessageReactions::where('message_id', $messageId)
            ->where('reacted_by', $userId)
            ->first();
    
        if (!$reaction) {
            return response()->json(['message' => 'Reaction not found'], 404);
        }
    
        // Delete the reaction
        $reaction->delete();
    
        // Optionally fetch updated message + reactions to broadcast
        $message = Message::with('reactions')->find($messageId);
    
        broadcast(new MessageReacted($message))->toOthers();
    
        return response()->json(['message' => 'Reaction removed successfully']);
    }

}
