<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Room;
use App\Models\User;
use Illuminate\Console\Command;

class CreateProjectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:projects {user_id} {--count=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create projects for a specific user using ProjectFactory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $count = $this->option('count');

        // Проверяем существование пользователя.
        $user = User::query()->find($userId);

        if (!$user) {
            $this->error("Пользователь с ID $userId не найден!");
            return 1;
        }

        $this->info("Создание $count проектов для пользователя с ID: $userId");

        // Создаем указанное количество комнат
        Project::factory()
            ->count($count)
            ->create([
                'user_id' => $userId,
            ]);

        $this->info("Успешно создано $count проектов для пользователя с ID: $userId");

        return 0;
    }
}
