<?php

namespace Database\Seeders\Support;

class WallShapes
{
    /**
     * @return list<array{length: float, angle: float, is_real: bool, order: int}>
     */
    public static function fromSpec(?string $shape, array $args = []): array
    {
        return match ($shape) {
            null, '', 'empty' => [],
            'rectangle' => self::rectangle((float) $args[0], (float) $args[1]),
            'opening' => self::withOpening(
                (float) $args[0],
                (float) $args[1],
                (float) ($args[2] ?? 90),
                (float) ($args[3] ?? 80),
            ),
            'lShape' => self::lShape(
                (float) ($args[0] ?? 500),
                (float) ($args[1] ?? 400),
                (float) ($args[2] ?? 200),
                (float) ($args[3] ?? 150),
            ),
            'corridor' => self::corridor((float) $args[0], (float) $args[1]),
            'pentagon' => self::pentagon((float) ($args[0] ?? 250)),
            default => throw new \InvalidArgumentException("Unknown wall shape [{$shape}]"),
        };
    }

    /**
     * Rectangle: north, east, south, west. Closed.
     *
     * @return list<array{length: float, angle: float, is_real: bool, order: int}>
     */
    public static function rectangle(float $northSouth, float $eastWest): array
    {
        return self::segments([
            [$northSouth, 90, true],
            [$eastWest, 0, true],
            [$northSouth, -90, true],
            [$eastWest, 180, true],
        ]);
    }

    /**
     * Rectangle with a door opening on the east side. Closed.
     *
     * @return list<array{length: float, angle: float, is_real: bool, order: int}>
     */
    public static function withOpening(
        float $northSouth,
        float $eastWest,
        float $opening = 90,
        float $offset = 80,
    ): array {
        $rest = $eastWest - $opening - $offset;
        if ($rest < 10) {
            $offset = max(10, $eastWest * 0.2);
            $opening = min($opening, $eastWest * 0.35);
            $rest = $eastWest - $opening - $offset;
        }

        return self::segments([
            [$northSouth, 90, true],
            [$offset, 0, true],
            [$opening, 0, false],
            [$rest, 0, true],
            [$northSouth, -90, true],
            [$eastWest, 180, true],
        ]);
    }

    /**
     * L-shape: overall width × depth with a rectangular notch. Closed.
     *
     * @return list<array{length: float, angle: float, is_real: bool, order: int}>
     */
    public static function lShape(
        float $width = 500,
        float $depth = 400,
        float $cutW = 200,
        float $cutH = 150,
    ): array {
        $armH = $depth - $cutH;
        $armW = $width - $cutW;

        return self::segments([
            [$width, 0, true],
            [$armH, 90, true],
            [$cutW, 180, true],
            [$cutH, 90, true],
            [$armW, 180, true],
            [$depth, -90, true],
        ]);
    }

    /**
     * @return list<array{length: float, angle: float, is_real: bool, order: int}>
     */
    public static function corridor(float $length, float $width): array
    {
        return self::rectangle($width, $length);
    }

    /**
     * Regular pentagon, exterior turn 72°. Closed.
     *
     * @return list<array{length: float, angle: float, is_real: bool, order: int}>
     */
    public static function pentagon(float $side = 250): array
    {
        $rows = [];
        $angle = 90.0;
        for ($i = 0; $i < 5; $i++) {
            $rows[] = [$side, self::normalizeAngle($angle), true];
            $angle += 72;
        }

        return self::segments($rows);
    }

    public static function perimeterMeters(array $walls): float
    {
        $cm = 0.0;
        foreach ($walls as $wall) {
            $cm += (float) $wall['length'];
        }

        return round($cm / 100, 2);
    }

    public static function areaSqMeters(array $walls): float
    {
        $x = 0.0;
        $y = 0.0;
        $points = [[0.0, 0.0]];

        foreach ($walls as $wall) {
            $rad = ((float) $wall['angle']) * M_PI / 180;
            $x += ((float) $wall['length'] / 100) * cos($rad);
            $y += ((float) $wall['length'] / 100) * sin($rad);
            $points[] = [$x, $y];
        }

        $area = 0.0;
        $n = count($points);
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $points[$i][0] * $points[$j][1];
            $area -= $points[$j][0] * $points[$i][1];
        }

        return round(abs($area) / 2, 2);
    }

    public static function realWallsAreaSqMeters(array $walls, float $heightMeters): float
    {
        $cm = 0.0;
        foreach ($walls as $wall) {
            if (! empty($wall['is_real'])) {
                $cm += (float) $wall['length'];
            }
        }

        return round(($cm / 100) * $heightMeters, 2);
    }

    /**
     * @param  list<array{0: float, 1: float, 2: bool}>  $rows
     * @return list<array{length: float, angle: float, is_real: bool, order: int}>
     */
    private static function segments(array $rows): array
    {
        $walls = [];
        $order = 1;
        foreach ($rows as $row) {
            $walls[] = [
                'length' => round((float) $row[0], 2),
                'angle' => round((float) $row[1], 2),
                'is_real' => (bool) $row[2],
                'order' => $order++,
            ];
        }

        return $walls;
    }

    private static function normalizeAngle(float $angle): float
    {
        $angle = fmod($angle, 360);
        if ($angle > 180) {
            $angle -= 360;
        }
        if ($angle <= -180) {
            $angle += 360;
        }

        return $angle;
    }
}
