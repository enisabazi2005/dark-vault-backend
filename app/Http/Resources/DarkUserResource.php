<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DarkUserResource extends JsonResource
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
            'name' => $this->name,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'password' => $this->password,
            'gender' => $this->gender,
            'age' => $this->age,
            'birthdate' => $this->birthdate,
            'picture' => $this->picture,
            'request_id' => $this->request_id,
            'online' => $this->online,
            'offline' => $this->offline,
            'away' => $this->away,
            'last_active_at' => $this->last_active_at,
            'has_pro' => $this->has_pro,
            'MAX_STORAGE' => $this->MAX_STORAGE,
            'view' => $this->view,
        ];
    }
}
