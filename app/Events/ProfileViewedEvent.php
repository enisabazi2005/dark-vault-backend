<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileViewedEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $viewerName;
    public $viewerId;
    public $viewedUserId;

    public function __construct($viewerName, $viewerId, $viewedUserId)
    {
        $this->viewerName = $viewerName;
        $this->viewerId = $viewerId;
        $this->viewedUserId = $viewedUserId;
    }

    public function broadcastOn()
    {
        return new Channel('profile-viewed.' . $this->viewedUserId);
    }

    public function broadcastAs()
    {
        return 'ProfileViewed';
    }
}
