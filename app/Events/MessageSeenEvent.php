<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class MessageSeenEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message_id;
    public $receiver_id;

    public function __construct($receiverId, $messageId)
    {
        $this->receiver_id = $receiverId;
        $this->message_id = $messageId;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->receiver_id); 
    }

    public function broadcastAs()
    {
        return 'MessageSeenEvent';
    }
}