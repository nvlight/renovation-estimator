<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomWall;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomWallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // sail artisan db:seed --class=RoomWallSeeder
        $rooms = Room::all();

        foreach ($rooms as $room) {
            // Создаем 4 стены для каждой комнаты (для примера, имитация четырехугольника)
            for ($i = 0; $i < 4; $i++) {
                RoomWall::factory()->create([
                    'room_id' => $room->id,
                    'length' => fake()->randomFloat(2, 2, 8), // Длина от 2 до 8 метров
                    'angle' => 90, // Угол 90 градусов для простого четырехугольника
                    'order' => $i, // Порядковый номер стены
                ]);
            }
        }
    }
}
