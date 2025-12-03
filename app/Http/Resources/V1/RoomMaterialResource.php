<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomMaterialResource extends JsonResource
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
            'room_id' => $this->room_id,
            'material_id' => $this->material_id,
            'room_title' => $this->room->name,
            'material_title' => $this->material->title,
            'amount' => $this->amount,
            'sum' => $this->sum,
            'notice' => $this->notice,
        ];
    }
}
