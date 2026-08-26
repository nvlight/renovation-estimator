<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Project;
use App\Models\Room;
use App\Models\RoomMaterial;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Database\Seeders\Support\MaterialCatalog;
use Database\Seeders\Support\WallShapes;
use Illuminate\Database\Seeder;

class RoomMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->pluck('id', 'email');
        $materials = Material::query()->get()->keyBy('id');
        $now = now();
        $rows = [];

        foreach (DemoDataset::projects() as $projectDef) {
            $userId = $users[$projectDef['owner_email']];
            $project = Project::query()
                ->where('user_id', $userId)
                ->where('name', $projectDef['name'])
                ->firstOrFail();

            foreach ($projectDef['rooms'] as $roomDef) {
                if ($roomDef['material_ids'] === []) {
                    continue;
                }

                $room = Room::query()
                    ->where('project_id', $project->id)
                    ->where('name', $roomDef['name'])
                    ->with('room_walls')
                    ->firstOrFail();

                $walls = $room->room_walls->map(fn ($wall) => [
                    'length' => (float) $wall->length,
                    'angle' => (float) $wall->angle,
                    'is_real' => (bool) $wall->is_real,
                ])->all();

                $perimeter = $walls === [] ? 10.0 : WallShapes::perimeterMeters($walls);
                $area = $walls === [] ? 10.0 : WallShapes::areaSqMeters($walls);

                foreach ($roomDef['material_ids'] as $materialId) {
                    $material = $materials->get($materialId);
                    if ($material === null) {
                        throw new \RuntimeException("Material [{$materialId}] is missing before RoomMaterialSeeder");
                    }

                    $amount = $this->amountFor($materialId, $perimeter, $area);
                    $rows[] = [
                        'room_id' => $room->id,
                        'material_id' => $materialId,
                        'amount' => $amount,
                        'sum' => MaterialCatalog::lineSum($material->title, $material->price, $amount),
                        'notice' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if ($rows !== []) {
            RoomMaterial::query()->insert($rows);
        }
    }

    private function amountFor(int $materialId, float $perimeter, float $area): int
    {
        return match (true) {
            in_array($materialId, [3, 4, 7, 8, 9, 10], true) => max(4, (int) ceil($perimeter / 3)),
            in_array($materialId, [5, 19], true) => max(4, (int) ceil($area * 0.7)),
            in_array($materialId, [6, 11, 14, 15, 16, 20, 35, 36], true) => max(20, (int) ceil($perimeter / 0.2)),
            in_array($materialId, [1, 2, 18], true) => max(2, (int) ceil(($area + $perimeter) / 3)),
            in_array($materialId, [29, 30], true) => max(4, (int) ceil($perimeter / 2.5) + 1),
            in_array($materialId, [21, 22, 23, 33, 34], true) => max(1, (int) ceil($area / 8)),
            default => max(1, (int) ceil($area / 12)),
        };
    }
}
