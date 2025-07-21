<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomWall>
 */
class RoomWallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $room = Room::query()->inRandomOrder()->first();

        return [
            'room_id' => $room->id,
            'length' => $this->faker->randomFloat(2, 1, 10), // Длина от 1 до 10 метров
            'angle' => $this->faker->randomFloat(2, 0, 359.99), // Угол от 0 до 359.99 градусов
            'order' => $this->faker->numberBetween(0, 5), // Порядковый номер стены (0-5 для примера)
        ];
    }
}
