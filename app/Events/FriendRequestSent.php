<?php

namespace App\Events;

use App\Models\DarkUsers;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $sender;
    public $receiver;

    public function __construct(DarkUsers $sender , DarkUsers $receiver)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    public function broadcastOn()
    {
        // dd($this->receiver->request_id);

        return new Channel("friend-request.{$this->receiver->request_id}");
    }

    public function broadcastAs()
    {
        return 'FriendRequestSent';
    }

    public function broadcastWith()
    {
        return [
            'sender' => [
                'request_id' => $this->sender->request_id,
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'picture' => $this->sender->picture
            ]
        ];
    }
}
