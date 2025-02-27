<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserMuteResource extends JsonResource
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
            'dark_users_id' => $this->dark_users_id,
            'muted' => $this->muted,
            'muted_id' => $this->muted_id,
        ];
    }
}
