<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomMaterial>
 */
class RoomMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $room_id = Room::query()->inRandomOrder()->first()->id;
        $material_id = Material::query()->inRandomOrder()->first()->id;

        return [
            'room_id' => $room_id,
            'material_id' => $material_id,
            'amount' => fake()->numberBetween(1, 11),
            'sum' => fake()->randomFloat(2, 1, 1000000),
            'notice' => fake('ru_RU')->realText(55),
        ];
    }
}
