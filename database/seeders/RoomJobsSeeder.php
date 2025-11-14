<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class RoomJobsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // sail artisan db:seed --class=RoomJobsSeeder
        $roomIds = DB::table('rooms')->pluck('id');

        $faker = Faker::create('ru_RU');

        if ($roomIds->isEmpty()) {
            return;
        }

        $jobs = [];

        foreach ($roomIds as $roomId) {

            // Для каждой комнаты создаём 2–5 работ
            $count = rand(2, 5);

            for ($i = 0; $i < $count; $i++) {
                $jobs[] = [
                    'room_id'    => $roomId,
                    'title'      => $faker->sentence(3),   // короткое название
                    'sum'        => $faker->numberBetween(500, 5000),
                    'more_info'  => $faker->realText(120), // нормальное описание
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('room_jobs')->insert($jobs);
    }
}
