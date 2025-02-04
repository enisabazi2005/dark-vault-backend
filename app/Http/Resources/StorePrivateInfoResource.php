<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorePrivateInfoResource extends JsonResource
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
            'name' => $this->name,
            'info_1' => $this->info_1,
            'info_2' => $this->info_2,
            'info_3' => $this->info_3,
        ];
    }
}
