<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomWallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            //'id' => $this->id,
            //'project_id' => $this->room->project_id,
            //'room_id' => $this->room_id,
            'length' => (double)($this->length),
            'angle' => (float)($this->angle),
            'is_real' => (integer)($this->is_real),
            //'order' => intval($this->order),
        ];
    }
}
