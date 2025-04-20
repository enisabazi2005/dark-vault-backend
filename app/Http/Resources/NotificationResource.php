<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'dark_user_id' => $this->dark_user_id,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'message_id' => $this->message_id,
        ];
    }
}
