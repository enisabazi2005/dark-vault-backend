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
use App\Models\DarkUsers;

class FriendRequestAccepted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $sender;
    public $receiver;

    public function __construct(DarkUsers $sender, DarkUsers $receiver)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    public function broadcastOn()
    {
        return [
            new Channel("friend-accept.{$this->sender->request_id}"),
            new Channel("friend-accept.{$this->receiver->request_id}")
        ];
    }

    public function broadcastAs()
    {
        return 'FriendRequestAccepted';
    }
}

