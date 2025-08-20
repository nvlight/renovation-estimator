<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Room;
use Illuminate\Console\Command;

class CreateRoomsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:rooms {project_id} {--count=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create rooms for a specific project using RoomFactory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $projectId = $this->argument('project_id');
        $count = $this->option('count');

        // Проверяем существование проекта
        $project = Project::query()->find($projectId);

        if (!$project) {
            $this->error("Проект с ID $projectId не найден!");
            return 1;
        }

        $this->info("Создание $count комнат для проекта ID: $projectId");

        // Создаем указанное количество комнат
        Room::factory()
            ->count($count)
            ->create([
                'project_id' => $projectId,
            ]);

        $this->info("Успешно создано $count комнат для проекта ID: $projectId");

        return 0;
    }
}
