<?php

namespace App\Http\Resources\V1;

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
            'place_name'  => $this->place_name,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
        ];
    }
}
