<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Room;
use App\Models\RoomWall;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Database\Seeders\Support\WallShapes;
use Illuminate\Database\Seeder;

class RoomWallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id', 'email');
        $now = now();
        $rows = [];

        foreach (DemoDataset::projects() as $projectDef) {
            $userId = $users[$projectDef['owner_email']];
            $project = Project::query()
                ->where('user_id', $userId)
                ->where('name', $projectDef['name'])
                ->firstOrFail();

            foreach ($projectDef['rooms'] as $roomDef) {
                $room = Room::query()
                    ->where('project_id', $project->id)
                    ->where('name', $roomDef['name'])
                    ->firstOrFail();

                $walls = WallShapes::fromSpec($roomDef['shape'], $roomDef['shape_args']);
                foreach ($walls as $wall) {
                    $rows[] = [
                        'room_id' => $room->id,
                        'length' => $wall['length'],
                        'angle' => $wall['angle'],
                        'is_real' => $wall['is_real'] ? 1 : 0,
                        'order' => $wall['order'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if ($rows !== []) {
            foreach (array_chunk($rows, 500) as $chunk) {
                RoomWall::query()->insert($chunk);
            }
        }
    }
}
