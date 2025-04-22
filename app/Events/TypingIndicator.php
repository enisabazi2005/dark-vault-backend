<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TypingIndicator implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender_id;
    public $receiver_id;
    public $is_typing;

    /**
     * Create a new event instance.
     */
    public function __construct($sender_id , $receiver_id, $is_typing)
    {
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->is_typing = $is_typing;
    }

    public function broadcastOn()
    {
        return new Channel('chatroom.' . $this->receiver_id);
    }

    public function broadcastWith()
    {
        return [
            'sender_id' => $this->sender_id,
            'is_typing' => $this->is_typing,
        ];
    }

    public function broadcastAs()
    {
        return 'user.typing';
    }
}
