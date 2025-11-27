<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "product_code" => $this->product_code,
            "price" => $this->price,
            "is_free" => false,
            "characteristics" => $this->characteristics,
            "advantages" => $this->advantages,
            "packaging_info" => $this->packaging_info,
            "brand" => $this->brand,
            "producing_country" => $this->producing_country,
            "images" => MaterialImageResource::collection($this->images),
        ];
    }
}
