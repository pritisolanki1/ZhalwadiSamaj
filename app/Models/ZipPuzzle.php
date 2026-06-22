<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipPuzzle extends Model
{
    protected $fillable = [
        'grid_size',
        'grid_numbers',
        'solution_path',
        'puzzle_date',
        'difficulty',
    ];

    protected $casts = [
        'grid_numbers' => 'array',
        'solution_path' => 'array',
        'puzzle_date' => 'date',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('puzzle_date', today());
    }

    public function results()
    {
        return $this->hasMany(ZipGameResult::class, 'puzzle_id');
    }

    public static function generateForDate($date)
    {
        $seed = crc32($date->format('Y-m-d'));
        srand($seed);

        $dayOfMonth = (int) $date->format('j');

        $diffIndex = $dayOfMonth % 3;

        switch ($diffIndex) {
            case 0:
                $difficulty = 'easy';
                $size = 5;
                $numWaypoints = rand(4, 6);
                break;
            case 1:
                $difficulty = 'medium';
                $size = 6;
                $numWaypoints = rand(7, 9);
                break;
            default:
                $difficulty = 'hard';
                $size = 7;
                $numWaypoints = rand(10, 14);
                break;
        }

        $total = $size * $size;
        if ($numWaypoints > $total) $numWaypoints = $total;

        // 1. Generate a base snake path
        $patternIndex = $dayOfMonth % 4;
        switch ($patternIndex) {
            case 0: $path = self::horizontalSnake($size, true); break;
            case 1: $path = self::verticalSnake($size, true); break;
            case 2: $path = self::horizontalSnake($size, false); break;
            default: $path = self::verticalSnake($size, false); break;
        }

        // 2. Apply random 2-opt perturbations to create organic-looking paths
        //    Higher difficulty = more perturbations = more complex path
        $numPerturbations = $size * ($diffIndex + 1) * 2;
        $path = self::perturbPath($path, $numPerturbations, $seed);

        // 3. Place waypoints along the perturbed path
        $waypointIndices = self::pickWaypoints($total, $numWaypoints);

        $gridNumbers = [];
        foreach ($waypointIndices as $num => $idx) {
            $pos = $path[$idx];
            $gridNumbers[] = [
                'row' => $pos[0],
                'col' => $pos[1],
                'number' => $num + 1,
            ];
        }

        return self::create([
            'grid_size' => $size,
            'grid_numbers' => $gridNumbers,
            'solution_path' => $path,
            'puzzle_date' => $date,
            'difficulty' => $difficulty,
        ]);
    }

    /**
     * Apply 2-opt perturbations to a Hamiltonian path.
     * Reverses random segments while maintaining adjacency at boundaries.
     * This creates organic-looking paths that don't follow a simple pattern.
     */
    public static function perturbPath(array $path, int $iterations, int $seed): array
    {
        $n = count($path);
        if ($n < 4) return $path;

        srand($seed);
        $result = $path;

        for ($attempt = 0; $attempt < $iterations * 3; $attempt++) {
            $i = rand(1, $n - 3);
            $j = rand($i + 2, min($i + 8, $n - 2));

            if ($j - $i < 2) continue;

            // Check if reversing segment [i..j] maintains adjacencies
            // result[i-1] must be adjacent to result[j]
            $ri = $result[$i - 1];
            $rj = $result[$j];
            if (abs($ri[0] - $rj[0]) + abs($ri[1] - $rj[1]) != 1) continue;

            // result[i] must be adjacent to result[j+1]
            $ri2 = $result[$i];
            $rj2 = $result[$j + 1];
            if (abs($ri2[0] - $rj2[0]) + abs($ri2[1] - $rj2[1]) != 1) continue;

            // Valid 2-opt swap: reverse the segment
            $segment = array_slice($result, $i, $j - $i + 1);
            $reversed = array_reverse($segment);
            array_splice($result, $i, $j - $i + 1, $reversed);
        }

        return $result;
    }

    public static function horizontalSnake($size, $startLeft = true)
    {
        $path = [];
        for ($row = 0; $row < $size; $row++) {
            $leftToRight = ($row % 2 === 0) ? $startLeft : !$startLeft;
            if ($leftToRight) {
                for ($col = 0; $col < $size; $col++) {
                    $path[] = [$row, $col];
                }
            } else {
                for ($col = $size - 1; $col >= 0; $col--) {
                    $path[] = [$row, $col];
                }
            }
        }
        return $path;
    }

    public static function verticalSnake($size, $startTop = true)
    {
        $path = [];
        for ($col = 0; $col < $size; $col++) {
            $topToBottom = ($col % 2 === 0) ? $startTop : !$startTop;
            if ($topToBottom) {
                for ($row = 0; $row < $size; $row++) {
                    $path[] = [$row, $col];
                }
            } else {
                for ($row = $size - 1; $row >= 0; $row--) {
                    $path[] = [$row, $col];
                }
            }
        }
        return $path;
    }

    private static function pickWaypoints(int $total, int $numWaypoints): array
    {
        if ($numWaypoints <= 2) {
            return $numWaypoints === 1 ? [0] : [0, $total - 1];
        }

        $indices = [0];
        $remaining = $numWaypoints - 2;
        $step = ($total - 1) / ($numWaypoints - 1);
        for ($i = 1; $i <= $remaining; $i++) {
            $indices[] = min((int) round($i * $step), $total - 1);
        }
        $indices[] = $total - 1;

        $indices = array_unique($indices);
        sort($indices);
        return array_values(array_slice($indices, 0, $numWaypoints));
    }
}
