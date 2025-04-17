<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\DarkUsers;

class FriendRequestRemoved implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $user;
    public $unfriended;

    public function __construct(DarkUsers $user, DarkUsers $unfriended)
    {
        $this->user = $user;
        $this->unfriended = $unfriended;
    }

    public function broadcastOn()
    {
        return new Channel("friend-remove.{$this->unfriended->request_id}");
    }

    public function broadcastAs()
    {
        return 'FriendRemoved';
    }
}