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
        $allWalls = [];

        foreach ($rooms as $room) {
            // Генерируем ширину и высоту для прямоугольника (чтобы четырехугольник сходился)
            $width = fake()->randomFloat(2, 200, 700); // Ширина от 2 до 8 метров
            $height = fake()->randomFloat(2, 100, 700); // Высота от 2 до 8 метров

            // Подготавливаем массив для bulk insert
            $allWalls[] = [
                'room_id' => $room->id,
                'length' => $width,
                'angle' => 90,
                'is_real' => true,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $allWalls[] = [
                'room_id' => $room->id,
                'length' => $height,
                'angle' => 0,
                'is_real' => true,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $allWalls[] = [
                'room_id' => $room->id,
                'length' => $width,
                'angle' => -90,
                'is_real' => true,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $allWalls[] = [
                'room_id' => $room->id,
                'length' => $height,
                'angle' => 180,
                'is_real' => true,
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert всех стен одним запросом
        RoomWall::query()->insert($allWalls);
    }
}
