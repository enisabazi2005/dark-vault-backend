<?php

namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class GroupMessageDeleted implements ShouldBroadcast
{
    use SerializesModels;

    public $messageId;
    public $groupId;

    public function __construct($groupId, $messageId)
    {
        $this->groupId = $groupId;
        $this->messageId = $messageId;
    }

    public function broadcastOn()
    {
        return new Channel('group.' . $this->groupId);
    }

    public function broadcastAs()
    {
        return 'group.message.deleted';
    }
}