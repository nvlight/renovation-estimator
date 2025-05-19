<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $this->faker->locale = 'ru_RU';

        $user = User::query()->inRandomOrder()->first();

        // Генерация случайного города или села с помощью Faker
        $placeName = $this->faker->randomElement([
            $this->faker->city,
            $this->faker->city . ' село',
            null, // Иногда место может быть null
        ]);

        // Генерация координат, если место указано
        $latitude = $placeName ? $this->faker->latitude : null;
        $longitude = $placeName ? $this->faker->longitude : null;

        return [
            'user_id' => $user->id,
            'name' => $this->faker->sentence(3), // Название проекта из 3 слов
            //'description' => $this->faker->optional()->paragraph, // Описание (иногда null)
            'description' => $this->faker->optional()->text(255),
            'created' => Carbon::now(), // Текущая дата и время
            'place_name' => $placeName,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }
}
