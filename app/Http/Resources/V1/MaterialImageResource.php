<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MaterialImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Базовый URL из config[](http://localhost)
        $baseUrl = rtrim(config('app.url'), '/');

        // Порт из .env (8012)
        $port = env('APP_PORT');

        // Если порт не стандартный (80/443 для http/https), добавляем его
        $fullHost = $port && !in_array($port, ['80', '443'])
            ? $baseUrl . ':' . $port
            : $baseUrl;

        // Относительный путь к файлу: /storage/ + name (без Storage::url())
        $relativePath = '/storage/' . ltrim($this->name, '/');  // ltrim на случай ведущего слеша в name

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'sort' => $this->sort,
            'name' => $this->name,
            'url' => $fullHost . $relativePath,  // http://localhost:8012/storage/materials/file.webp
        ];
    }
}
