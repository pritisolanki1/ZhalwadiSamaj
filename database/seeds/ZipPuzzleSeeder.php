<?php

namespace Database\Seeders;

use App\Models\ZipPuzzle;
use Illuminate\Database\Seeder;

class ZipPuzzleSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [0,  5, 5,  'easy',   2],
            [1,  5, 6,  'easy',   3],
            [2,  6, 7,  'medium', 6],
            [3,  6, 8,  'medium', 8],
            [4,  6, 10, 'medium', 10],
            [5,  7, 10, 'hard',   20],
            [6,  7, 12, 'hard',   25],
            [7,  5, 4,  'easy',   2],
            [8,  6, 9,  'medium', 8],
            [9,  7, 14, 'hard',   28],
            [10, 5, 7,  'easy',   4],
            [11, 7, 11, 'hard',   22],
            [12, 6, 6,  'easy',   5],
            [13, 7, 13, 'hard',   30],
        ];

        foreach ($configs as $i => $cfg) {
            [$dayOffset, $size, $numWaypoints, $difficulty, $perturbations] = $cfg;
            $total = $size * $size;
            if ($numWaypoints > $total) $numWaypoints = $total;

            $startLeft = $i % 2 === 0;
            $useVertical = $i % 3 === 0;
            $base = $useVertical
                ? ZipPuzzle::verticalSnake($size, $startLeft)
                : ZipPuzzle::horizontalSnake($size, $startLeft);

            // Apply perturbations for non-trivial paths
            $path = ZipPuzzle::perturbPath($base, $perturbations, $i * 100 + 42);

            $indices = [0];
            $remaining = $numWaypoints - 2;
            if ($remaining > 0) {
                $step = ($total - 1) / ($numWaypoints - 1);
                for ($j = 1; $j <= $remaining; $j++) {
                    $indices[] = (int) round($j * $step);
                }
            }
            $indices[] = $total - 1;
            $indices = array_unique($indices);
            sort($indices);
            $indices = array_values(array_slice($indices, 0, $numWaypoints));

            $gridNumbers = [];
            foreach ($indices as $num => $idx) {
                $pos = $path[$idx];
                $gridNumbers[] = [
                    'row' => $pos[0],
                    'col' => $pos[1],
                    'number' => $num + 1,
                ];
            }

            ZipPuzzle::create([
                'grid_size' => $size,
                'grid_numbers' => $gridNumbers,
                'solution_path' => $path,
                'puzzle_date' => now()->addDays($dayOffset)->format('Y-m-d'),
                'difficulty' => $difficulty,
            ]);
        }
    }
}
