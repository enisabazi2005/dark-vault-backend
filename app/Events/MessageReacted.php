<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return [
            new Channel('chatroom.' . $this->message->sender_id),
            new Channel('chatroom.' . $this->message->reciever_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->message->id,
            'reactions' => $this->message->reactions->map(function ($r) {
                return [
                    'reacted_by' => $r->reacted_by,
                    'reaction_type' => $r->reaction_type,
                ];
            }),
        ];
    }
}
