<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'name'        => $this->name,
            'description' => $this->description,
            'created' => $this->created_at->format('Y-m-d H:i:s'),
            'location' => [
                'name' => $this->place_name,
                'lat' => $this->latitude !== null ? number_format($this->latitude, 8, '.', '') : null,
                'lng' => $this->longitude !== null ? number_format($this->longitude, 8, '.', '') : null,
            ],
        ];
    }
}
