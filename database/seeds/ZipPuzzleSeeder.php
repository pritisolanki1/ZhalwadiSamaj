<?php

namespace Database\Seeders;

use App\Models\ZipPuzzle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ZipPuzzleSeeder extends Seeder
{
    public function run(): void
    {
        $puzzles = [
            [
                'puzzle_date'   => Carbon::today()->toDateString(),
                'difficulty'    => 'medium',
                'answer_word'   => 'SPARK',
                'grid_data'     => [
                    ['S', 'T', 'L', 'M', 'N'],
                    ['P', 'A', 'R', 'K', 'O'],
                    ['B', 'C', 'D', 'E', 'F'],
                    ['G', 'H', 'I', 'J', 'L'],
                    ['Q', 'R', 'S', 'T', 'U'],
                ],
                'solution_path' => [[0, 0], [1, 0], [1, 1], [1, 2], [1, 3]],
            ],
            [
                'puzzle_date'   => Carbon::today()->addDay()->toDateString(),
                'difficulty'    => 'easy',
                'answer_word'   => 'OCEAN',
                'grid_data'     => [
                    ['A', 'B', 'C', 'D', 'O'],
                    ['E', 'C', 'E', 'A', 'N'],
                    ['F', 'G', 'H', 'I', 'J'],
                    ['K', 'L', 'M', 'N', 'P'],
                    ['Q', 'R', 'S', 'T', 'U'],
                ],
                'solution_path' => [[0, 4], [1, 4], [1, 3], [1, 2], [1, 1]],
            ],
            [
                'puzzle_date'   => Carbon::today()->addDays(2)->toDateString(),
                'difficulty'    => 'hard',
                'answer_word'   => 'BRIDGE',
                'grid_data'     => [
                    ['B', 'R', 'I', 'D', 'G', 'E'],
                    ['A', 'C', 'F', 'H', 'J', 'K'],
                    ['L', 'M', 'N', 'P', 'Q', 'S'],
                    ['T', 'U', 'V', 'W', 'X', 'Y'],
                    ['Z', 'A', 'B', 'C', 'D', 'E'],
                ],
                'solution_path' => [[0, 0], [0, 1], [0, 2], [0, 3], [0, 4], [0, 5]],
            ],
            [
                'puzzle_date'   => Carbon::today()->addDays(3)->toDateString(),
                'difficulty'    => 'medium',
                'answer_word'   => 'CLOUD',
                'grid_data'     => [
                    ['C', 'L', 'O', 'U', 'D'],
                    ['E', 'F', 'G', 'H', 'I'],
                    ['J', 'K', 'M', 'N', 'P'],
                    ['Q', 'R', 'S', 'T', 'V'],
                    ['W', 'X', 'Y', 'Z', 'A'],
                ],
                'solution_path' => [[0, 0], [0, 1], [0, 2], [0, 3], [0, 4]],
            ],
            [
                'puzzle_date'   => Carbon::today()->addDays(4)->toDateString(),
                'difficulty'    => 'medium',
                'answer_word'   => 'FLAME',
                'grid_data'     => [
                    ['F', 'L', 'A', 'M', 'E'],
                    ['B', 'C', 'D', 'G', 'H'],
                    ['I', 'J', 'K', 'N', 'O'],
                    ['P', 'Q', 'R', 'S', 'T'],
                    ['U', 'V', 'W', 'X', 'Y'],
                ],
                'solution_path' => [[0, 0], [0, 1], [0, 2], [0, 3], [0, 4]],
            ],
            [
                'puzzle_date'   => Carbon::today()->addDays(5)->toDateString(),
                'difficulty'    => 'hard',
                'answer_word'   => 'MAGNET',
                'grid_data'     => [
                    ['M', 'A', 'G', 'N', 'E', 'T'],
                    ['B', 'C', 'D', 'F', 'H', 'I'],
                    ['J', 'K', 'L', 'O', 'P', 'Q'],
                    ['R', 'S', 'U', 'V', 'W', 'X'],
                    ['Y', 'Z', 'A', 'B', 'C', 'D'],
                ],
                'solution_path' => [[0, 0], [0, 1], [0, 2], [0, 3], [0, 4], [0, 5]],
            ],
            [
                'puzzle_date'   => Carbon::today()->addDays(6)->toDateString(),
                'difficulty'    => 'easy',
                'answer_word'   => 'DRAGON',
                'grid_data'     => [
                    ['D', 'R', 'A', 'G', 'O', 'N'],
                    ['B', 'C', 'E', 'F', 'H', 'I'],
                    ['J', 'K', 'L', 'M', 'P', 'Q'],
                    ['S', 'T', 'U', 'V', 'W', 'X'],
                    ['Y', 'Z', 'A', 'B', 'C', 'D'],
                ],
                'solution_path' => [[0, 0], [0, 1], [0, 2], [0, 3], [0, 4], [0, 5]],
            ],
        ];

        foreach ($puzzles as $puzzle) {
            ZipPuzzle::create($puzzle);
        }
    }
}
