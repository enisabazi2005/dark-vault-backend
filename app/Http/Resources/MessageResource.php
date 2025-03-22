<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'reciever_id' => $this->reciever_id,
            'message' => $this->message,
            'dark_users_id' => $this->dark_users_id,
            'order' => $this->order,
            'message_sent_at' => $this->message_sent_at,
            'seen_at' => $this->seen_at,
        ];
    }
}
