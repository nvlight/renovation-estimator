<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Room;
use App\Models\RoomJob;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Database\Seeders\Support\WallShapes;
use Illuminate\Database\Seeder;

class RoomJobsSeeder extends Seeder
{
    private const PRICE_STRETCH = 600;

    private const PRICE_DRYWALL = 600;

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
                if ($roomDef['jobs'] === []) {
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

                if ($walls === []) {
                    continue;
                }

                $area = WallShapes::areaSqMeters($walls);
                $perimeter = WallShapes::perimeterMeters($walls);
                $wallsArea = WallShapes::realWallsAreaSqMeters($walls, (float) $room->height);

                foreach ($roomDef['jobs'] as $job) {
                    $payload = match ($job) {
                        'stretch' => $this->stretchCeiling($room->id, $area, $perimeter),
                        'drywall_ceil' => $this->drywall($room->id, 'Гипсокартон, потолок', $area),
                        'drywall_walls' => $this->drywall($room->id, 'Гипсокартон, стены', $wallsArea),
                        default => throw new \InvalidArgumentException("Unknown job [{$job}]"),
                    };

                    $rows[] = [
                        'room_id' => $payload['room_id'],
                        'title' => $payload['title'],
                        'sum' => $payload['sum'],
                        'more_info' => json_encode($payload['more_info'], JSON_UNESCAPED_UNICODE),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if ($rows !== []) {
            RoomJob::query()->insert($rows);
        }
    }

    /**
     * @return array{room_id: int, title: string, sum: int, more_info: list<array{name: string, amount: int|float, price: int, sum: int|float}>}
     */
    private function stretchCeiling(int $roomId, float $area, float $perimeter): array
    {
        $ceilM2 = (int) max(10, ceil($area));
        $baguettes = (int) ceil($perimeter);
        if ($baguettes % 2 !== 0) {
            $baguettes++;
        }
        $baguettes += 2;

        $moreInfo = [
            $this->line('Потолок + установка', $ceilM2, self::PRICE_STRETCH),
            $this->line('Багеты', $baguettes, 55),
            $this->line('Люстры', 1, 200),
            $this->line('Доставка', 1, 1000),
        ];

        return [
            'room_id' => $roomId,
            'title' => 'Натяжной потолок',
            'sum' => (int) array_sum(array_column($moreInfo, 'sum')),
            'more_info' => $moreInfo,
        ];
    }

    /**
     * @return array{room_id: int, title: string, sum: int, more_info: list<array{name: string, amount: int, price: int, sum: int}>}
     */
    private function drywall(int $roomId, string $title, float $area): array
    {
        $m2 = (int) max(1, round($area));
        $moreInfo = [
            $this->line($title, $m2, self::PRICE_DRYWALL),
        ];

        return [
            'room_id' => $roomId,
            'title' => $title,
            'sum' => (int) $moreInfo[0]['sum'],
            'more_info' => $moreInfo,
        ];
    }

    /**
     * @return array{name: string, amount: int, price: int, sum: int}
     */
    private function line(string $name, int $amount, int $price): array
    {
        return [
            'name' => $name,
            'amount' => $amount,
            'price' => $price,
            'sum' => $amount * $price,
        ];
    }
}
