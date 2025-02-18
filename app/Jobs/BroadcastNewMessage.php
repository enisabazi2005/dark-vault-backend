<?php

namespace App\Jobs;

use App\Events\NewMessage;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastNewMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;
    public $sender_id;
    public $receiver_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message, $sender_id, $receiver_id)
    {
        $this->message = $message;
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Trigger the event when the job runs
        broadcast(new NewMessage($this->message, $this->sender_id, $this->receiver_id));
    }
}
