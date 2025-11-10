<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\RoomWall;
use Illuminate\Console\Command;

class CreateRoomWallsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'room-walls:create {room_id : The ID of the room} {--count=1 : The number of walls to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a specified number of walls for a given room';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roomId = $this->argument('room_id');
        $count = $this->option('count');

        // Проверяем, что room_id существует
        $room = Room::query()->find($roomId);
        if (!$room) {
            $this->error("Комната с id {$roomId} не найдена.");
            return 1;
        }

        // Проверяем, что count является положительным числом
        if (!is_numeric($count) || $count <= 0) {
            $this->error("Количество должно быть положительным целым числом.");
            return 1;
        }

        try {
            // Создаём стены с помощью фабрики
            RoomWall::factory()
                ->count((int)$count)
                ->create(['room_id' => $roomId]);

            $this->info("Успешно создано {$count} стен(а,ы) для комнаты с ID {$roomId}.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Получена ошибка: {$e->getMessage()}");
            return 1;
        }
    }
}
