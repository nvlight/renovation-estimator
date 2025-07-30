<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class CreateProjectsCommand extends Command
{
    protected $signature = 'projects:create {count : Количество проектов для создания} {user_id : ID пользователя}';

    protected $description = 'Создает указанное количество проектов для пользователя';

    public function handle(): int
    {
        $count = $this->argument('count');
        $userId = $this->argument('user_id');

        // Проверка на валидность входных параметров
        if (!is_numeric($count) || $count < 1) {
            $this->error('Количество проектов должно быть положительным числом.');
            return 1;
        }

        if (!is_numeric($userId) || $userId < 1) {
            $this->error('ID пользователя должно быть положительным числом.');
            return 1;
        }

        // Создание проектов через фабрику
        Project::factory()
            ->count((int)$count)
            ->create(['user_id' => (int)$userId]);

        $this->info("Успешно создано {$count} проектов для пользователя с ID {$userId}.");

        return 0;
    }
}
