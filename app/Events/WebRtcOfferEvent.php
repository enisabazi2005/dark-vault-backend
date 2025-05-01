<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRtcOfferEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $offer;
    public $answer;
    public $candidate;
    public $from;
    public $to;
    public $group_id;

    public function __construct($data)
    {
        $this->type = $data['type'];
        $this->offer = $data['offer'] ?? null;
        $this->answer = $data['answer'] ?? null;
        $this->candidate = $data['candidate'] ?? null;
        $this->from = $data['from'];
        $this->to = $data['to'];
        $this->group_id = $data['group_id'];
    }

    public function broadcastOn()
    {
        return new Channel('group.' . $this->group_id);
    }
}