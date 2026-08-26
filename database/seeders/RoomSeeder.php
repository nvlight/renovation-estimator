<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id', 'email');

        foreach (DemoDataset::projects() as $projectDef) {
            $userId = $users[$projectDef['owner_email']];
            $project = Project::query()
                ->where('user_id', $userId)
                ->where('name', $projectDef['name'])
                ->firstOrFail();

            foreach ($projectDef['rooms'] as $room) {
                Room::query()->create([
                    'project_id' => $project->id,
                    'name' => $room['name'],
                    'description' => $room['description'],
                    'height' => $room['height'],
                ]);
            }
        }
    }
}
