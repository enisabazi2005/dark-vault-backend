<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendRequestsResource extends JsonResource
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
            'dark_user_id' => $this->dark_user_id,
            'is_accepted' => $this->is_accepted,
            'friend' => $this->friend,
            'rejection' => $this->rejection,
            'pending' => $this->pending,
        ];
    }
}
