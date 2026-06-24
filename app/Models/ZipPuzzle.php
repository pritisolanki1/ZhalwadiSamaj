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

    const DIRS = [[0, 1], [1, 0], [0, -1], [-1, 0]];

    public function scopeToday($query)
    {
        return $query->whereDate('puzzle_date', today());
    }

    public function results()
    {
        return $this->hasMany(ZipGameResult::class, 'puzzle_id');
    }

    // ========================================================================
    //  PUBLIC GENERATOR
    // ========================================================================

    public static function generateForDate($date)
    {
        $seed = crc32($date->format('Y-m-d'));
        mt_srand($seed);

        $dayOfMonth = (int) $date->format('j');
        $diffIndex = $dayOfMonth % 3;

        switch ($diffIndex) {
            case 0:
                $difficulty = 'easy';
                $size = 5;
                $numWaypoints = mt_rand(4, 6);
                break;
            case 1:
                $difficulty = 'medium';
                $size = 6;
                $numWaypoints = mt_rand(7, 9);
                break;
            default:
                $difficulty = 'hard';
                $size = 7;
                $numWaypoints = mt_rand(10, 14);
                break;
        }

        $total = $size * $size;
        if ($numWaypoints > $total) {
            $numWaypoints = $total;
        }

        // 1. Generate a Hamiltonian path via randomized DFS with coiling bias.
        //    Higher diffIndex = harder difficulty = more randomness in path.
        $path = self::generateHamiltonianPath($size, mt_rand(), $diffIndex);

        // 2. Post-process with 2-opt perturbations to break up any remaining
        //    linear interior segments and create structural detours.
        $numPerturbations = $size * ($diffIndex + 1) * 4;
        $path = self::perturbPath($path, $numPerturbations, mt_rand());

        // 3. Place waypoints at random structural intervals.
        $waypointIndices = self::pickStructuralWaypoints($total, $numWaypoints, mt_rand());

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

    // ========================================================================
    //  HAMILTONIAN PATH GENERATION  (randomized DFS  +  coiling bias)
    // ========================================================================

    public static function generateHamiltonianPath(int $size, int $seed, int $complexity = 1): array
    {
        mt_srand($seed);

        $maxAttempts = $size <= 5 ? 200 : ($size <= 6 ? 100 : 10);
        $maxBacktracks = $size <= 5 ? 10000 : ($size <= 6 ? 50000 : 200000);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $startRow = mt_rand(0, $size - 1);
            $startCol = mt_rand(0, $size - 1);

            $visited = array_fill(0, $size, array_fill(0, $size, false));
            $path = [];
            $backtrackCount = 0;

            if (self::dfsCoil($path, $visited, $startRow, $startCol, $size, $complexity, $maxBacktracks, $backtrackCount)) {
                return $path;
            }
        }

        return self::horizontalSnake($size, true);
    }

    private static function dfsCoil(array &$path, array &$visited, int $row, int $col, int $size, int $complexity, int $maxBacktracks, int &$backtrackCount): bool
    {
        $visited[$row][$col] = true;
        $path[] = [$row, $col];

        if (count($path) === $size * $size) {
            return true;
        }

        $neighbors = self::getOrderedNeighbors($row, $col, $visited, $size, $complexity);

        foreach ($neighbors as $n) {
            if (self::dfsCoil($path, $visited, $n[0], $n[1], $size, $complexity, $maxBacktracks, $backtrackCount)) {
                return true;
            }

            $backtrackCount++;
            if ($backtrackCount > $maxBacktracks) {
                array_pop($path);
                $visited[$row][$col] = false;
                return false;
            }
        }

        array_pop($path);
        $visited[$row][$col] = false;
        return false;
    }

    private static function getOrderedNeighbors(int $row, int $col, array &$visited, int $size, int $complexity): array
    {
        $neighbors = [];

        foreach (self::DIRS as $d) {
            $nr = $row + $d[0];
            $nc = $col + $d[1];

            if ($nr < 0 || $nr >= $size || $nc < 0 || $nc >= $size || $visited[$nr][$nc]) {
                continue;
            }

            $degree = 0;
            $wallCount = 0;
            foreach (self::DIRS as $d2) {
                $nnr = $nr + $d2[0];
                $nnc = $nc + $d2[1];
                if ($nnr < 0 || $nnr >= $size || $nnc < 0 || $nnc >= $size) {
                    $wallCount++;
                } elseif (!$visited[$nnr][$nnc]) {
                    $degree++;
                }
            }

            $layer = min($nr, $nc, $size - 1 - $nr, $size - 1 - $nc);

            $neighbors[] = [$nr, $nc, $degree, $wallCount, $layer];
        }

        usort($neighbors, function ($a, $b) {
            $scoreA = $a[2] * 100000 - $a[3] * 1000 + $a[4];
            $scoreB = $b[2] * 100000 - $b[3] * 1000 + $b[4];

            if ($scoreA !== $scoreB) {
                return $scoreA - $scoreB;
            }

            return mt_rand(-1, 1);
        });

        if ($complexity > 0 && count($neighbors) > 1) {
            $numSwaps = min($complexity, count($neighbors) - 1);
            for ($i = 0; $i < $numSwaps; $i++) {
                $j = mt_rand($i, count($neighbors) - 1);
                if ($i !== $j) {
                    $tmp = $neighbors[$i];
                    $neighbors[$i] = $neighbors[$j];
                    $neighbors[$j] = $tmp;
                }
            }
        }

        return $neighbors;
    }

    // ========================================================================
    //  2-OPT PERTURBATION  (post-processing for structural detours)
    // ========================================================================

    public static function perturbPath(array $path, int $iterations, int $seed): array
    {
        $n = count($path);
        if ($n < 4) {
            return $path;
        }

        mt_srand($seed);
        $result = $path;
        $applied = 0;

        for ($attempt = 0; $attempt < $iterations * 5 && $applied < $iterations; $attempt++) {
            $i = mt_rand(1, $n - 3);
            $minJ = $i + 2;
            $maxJ = min($i + mt_rand(3, max(3, (int)($n * 0.35))), $n - 2);
            if ($maxJ < $minJ) {
                continue;
            }
            $j = mt_rand($minJ, $maxJ);

            $ri = $result[$i - 1];
            $rj = $result[$j];
            if (abs($ri[0] - $rj[0]) + abs($ri[1] - $rj[1]) !== 1) {
                continue;
            }

            $ri2 = $result[$i];
            $rj2 = $result[$j + 1];
            if (abs($ri2[0] - $rj2[0]) + abs($ri2[1] - $rj2[1]) !== 1) {
                continue;
            }

            $segment = array_slice($result, $i, $j - $i + 1);
            $reversed = array_reverse($segment);
            array_splice($result, $i, $j - $i + 1, $reversed);
            $applied++;
        }

        return $result;
    }

    // ========================================================================
    //  STRUCTURAL WAYPOINT PLACEMENT  (random intervals with jitter)
    // ========================================================================

    private static function pickStructuralWaypoints(int $total, int $numWaypoints, int $seed): array
    {
        mt_srand($seed);

        if ($numWaypoints <= 2) {
            return $numWaypoints === 1 ? [0] : [0, $total - 1];
        }

        $remaining = $numWaypoints - 2;
        if ($remaining <= 0) {
            return [0, $total - 1];
        }

        $positions = [];
        for ($i = 0; $i < $remaining; $i++) {
            $segmentSize = ($total - 2) / $remaining;
            $segmentStart = 1 + (int)($i * $segmentSize);
            $segmentEnd = 1 + (int)(($i + 1) * $segmentSize) - 1;
            if ($segmentEnd < $segmentStart) {
                $segmentEnd = $segmentStart;
            }
            $positions[] = mt_rand($segmentStart, $segmentEnd);
        }

        $jitterAmount = max(1, (int)($total * 0.08));
        foreach ($positions as $k => $pos) {
            if (mt_rand(0, 100) < 40) {
                $newPos = max(1, min($total - 2, $pos + mt_rand(-$jitterAmount, $jitterAmount)));
                $positions[$k] = $newPos;
            }
        }

        $indices = array_merge([0], $positions, [$total - 1]);
        $indices = array_unique($indices);
        sort($indices);

        return array_values(array_slice($indices, 0, $numWaypoints));
    }

    // ========================================================================
    //  SNAKE FALLBACKS
    // ========================================================================

    public static function horizontalSnake($size, $startLeft = true): array
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

    public static function verticalSnake($size, $startTop = true): array
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
}
