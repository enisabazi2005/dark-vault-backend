<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UnreadMessagesEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notifications;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct($notifications, $userId)
    {
        $this->userId = $userId;
        $this->notifications = $notifications;
        Log::info('Broadcasting unread messages event for user: ' . $this->userId, ['notifications' => $this->notifications]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('unread-messages-' . $this->userId);  
    }

    public function broadcastWith()
    {
        return [
            'notifications' => $this->notifications,
        ];
    }
}