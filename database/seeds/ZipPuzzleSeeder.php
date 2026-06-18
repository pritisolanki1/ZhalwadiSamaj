<?php

use App\Models\ZipPuzzle;
use Illuminate\Database\Seeder;

class ZipPuzzleSeeder extends Seeder
{
    public function run(): void
    {
        $puzzles = [
            $this->makePuzzle(0, 6, 7, 'easy'),
            $this->makePuzzle(1, 6, 8, 'easy'),
            $this->makePuzzle(2, 6, 10, 'medium'),
            $this->makePuzzle(3, 6, 12, 'medium'),
            $this->makePuzzle(4, 7, 10, 'medium'),
            $this->makePuzzle(5, 7, 12, 'medium'),
            $this->makePuzzle(6, 7, 15, 'hard'),
            $this->makePuzzle(7, 6, 6, 'easy'),
            $this->makePuzzle(8, 6, 9, 'medium'),
            $this->makePuzzle(9, 6, 14, 'hard'),
            $this->makePuzzle(10, 7, 8, 'easy'),
            $this->makePuzzle(11, 7, 14, 'medium'),
            $this->makePuzzle(12, 7, 18, 'hard'),
            $this->makePuzzle(13, 6, 16, 'hard'),
        ];

        foreach ($puzzles as $data) {
            ZipPuzzle::create($data);
        }
    }

    private function makePuzzle(int $dayOffset, int $gridSize, int $numberCount, string $difficulty): array
    {
        $snake = $this->generateHorizontalSnake($gridSize, $dayOffset % 2 === 0);
        $indices = $this->pickWaypointIndices($gridSize * $gridSize, $numberCount);

        $gridNumbers = [];
        foreach ($indices as $num => $pathIdx) {
            $gridNumbers[] = [
                'row' => $snake[$pathIdx][0],
                'col' => $snake[$pathIdx][1],
                'number' => $num + 1,
            ];
        }

        return [
            'grid_size' => $gridSize,
            'grid_numbers' => $gridNumbers,
            'solution_path' => $snake,
            'puzzle_date' => now()->addDays($dayOffset)->format('Y-m-d'),
            'difficulty' => $difficulty,
        ];
    }

    private function generateHorizontalSnake(int $size, bool $startLeft): array
    {
        $path = [];
        for ($row = 0; $row < $size; $row++) {
            if ($row % 2 === 0) {
                if ($startLeft) {
                    for ($col = 0; $col < $size; $col++) {
                        $path[] = [$row, $col];
                    }
                } else {
                    for ($col = $size - 1; $col >= 0; $col--) {
                        $path[] = [$row, $col];
                    }
                }
            } else {
                if ($startLeft) {
                    for ($col = $size - 1; $col >= 0; $col--) {
                        $path[] = [$row, $col];
                    }
                } else {
                    for ($col = 0; $col < $size; $col++) {
                        $path[] = [$row, $col];
                    }
                }
            }
        }
        return $path;
    }

    private function pickWaypointIndices(int $totalCells, int $numWaypoints): array
    {
        if ($numWaypoints <= 2) {
            return $numWaypoints === 1 ? [0] : [0, $totalCells - 1];
        }

        $indices = [0];
        $step = ($totalCells - 1) / ($numWaypoints - 1);
        for ($i = 1; $i < $numWaypoints - 1; $i++) {
            $indices[] = (int) round($i * $step);
        }
        $indices[] = $totalCells - 1;

        $indices = array_unique($indices);
        sort($indices);

        while (count($indices) < $numWaypoints) {
            $last = end($indices);
            $candidates = [];
            for ($i = 1; $i < $totalCells - 1; $i++) {
                if (!in_array($i, $indices)) {
                    $minDist = PHP_INT_MAX;
                    foreach ($indices as $idx) {
                        $dist = abs($i - $idx);
                        if ($dist < $minDist) $minDist = $dist;
                    }
                    $candidates[$i] = $minDist;
                }
            }
            arsort($candidates);
            $indices[] = key($candidates);
            sort($indices);
        }

        $indices = array_slice($indices, 0, $numWaypoints);
        sort($indices);
        return $indices;
    }
}
