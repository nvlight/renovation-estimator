<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id', 'email');

        foreach (DemoDataset::projects() as $project) {
            $userId = $users[$project['owner_email']] ?? null;
            if ($userId === null) {
                throw new \RuntimeException("User [{$project['owner_email']}] is missing before ProjectSeeder");
            }

            Project::query()->create([
                'user_id' => $userId,
                'name' => $project['name'],
                'description' => $project['description'],
                'created' => Carbon::now()->subDays((int) $project['days_ago']),
                'place_name' => $project['place_name'],
                'latitude' => $project['latitude'],
                'longitude' => $project['longitude'],
            ]);
        }
    }
}
