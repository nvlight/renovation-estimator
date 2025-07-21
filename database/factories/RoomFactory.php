<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        fake()->locale('ru_RU');

        $project = Project::query()->inRandomOrder()->first();

        return [
            'project_id' => $project->id,
            'name' => fake()->sentence(2), // Название комнаты из 1-2 слов на русском
            'description' => fake()->optional()->text(55),
            'height' => fake()->randomFloat(fake()->numberBetween(0, 2), 2.5, 3.5),
        ];
    }
}
