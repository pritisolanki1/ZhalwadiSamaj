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

        // 2. Post-process with 2-opt perturbations — multiple passes with
        //    increasing segment caps to break up linear snake patterns.
        $segmentCaps = [0.35, 0.60, 0.80];
        $perPassIterations = [$size * 4, $size * 8, $size * 12];

        $path = self::perturbPath($path, $perPassIterations[$diffIndex], mt_rand(), $segmentCaps[$diffIndex]);
        $path = self::perturbPath($path, max(5, (int)($perPassIterations[$diffIndex] / 2)), mt_rand(), $segmentCaps[$diffIndex] * 0.7);

        // 3. For medium/hard, verify the path has enough direction changes.
        //    A low-tortuosity path (e.g. snake) is too easy regardless of waypoints.
        if ($diffIndex >= 1) {
            $tortuosity = self::measureTortuosity($path);
            $threshold = $diffIndex >= 2 ? 0.40 : 0.30;
            if ($tortuosity < $threshold) {
                $path = self::perturbPath($path, $size * 20, mt_rand(), min(0.85, $segmentCaps[$diffIndex] + 0.2));
            }
        }

        // 4. Place waypoints at strategic structural positions with varied gap sizes.
        $waypointIndices = self::pickStrategicWaypoints($total, $numWaypoints, mt_rand(), $diffIndex);

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

    public static function perturbPath(array $path, int $iterations, int $seed, float $maxSegmentRatio = 0.35): array
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
            $maxJ = min($i + mt_rand(3, max(3, (int)($n * $maxSegmentRatio))), $n - 2);
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
    //  TORTUOSITY MEASUREMENT  (ratio of direction changes in path)
    // ========================================================================

    /**
     * Measure how twisty a path is. Returns the ratio of direction changes
     * to total steps. Closer to 1.0 = very twisty (hard), closer to 0.0 =
     * mostly straight (easy / snake-like).
     */
    public static function measureTortuosity(array $path): float
    {
        $n = count($path);
        if ($n < 3) {
            return 0.0;
        }

        $turns = 0;
        $prevDirR = null;
        $prevDirC = null;

        for ($i = 1; $i < $n; $i++) {
            $dr = $path[$i][0] - $path[$i - 1][0];
            $dc = $path[$i][1] - $path[$i - 1][1];

            if ($prevDirR !== null && ($dr !== $prevDirR || $dc !== $prevDirC)) {
                $turns++;
            }
            $prevDirR = $dr;
            $prevDirC = $dc;
        }

        return $turns / ($n - 1);
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
    //  STRATEGIC WAYPOINT PLACEMENT  (variable gap sizes, jitter)
    // ========================================================================

    /**
     * Place waypoints with intentionally variable gap sizes between them.
     * Harder difficulties get more irregular spacing (some gaps small,
     * others very large) which forces the player to think ahead.
     */
    private static function pickStrategicWaypoints(int $total, int $numWaypoints, int $seed, int $diffIndex): array
    {
        mt_srand($seed);

        if ($numWaypoints <= 2) {
            return $numWaypoints === 1 ? [0] : [0, $total - 1];
        }

        $waypoints = [0]; // first waypoint always at start

        $remaining = $numWaypoints - 2; // interior waypoints (excl. first & last)
        $available = $total - 2;        // interior cells

        if ($remaining <= 0) {
            return [0, $total - 1];
        }

        // Build variable-sized gaps by randomly distributing the available cells
        // into remaining+1 segments (one extra to create irregularity before the last waypoint)
        $gaps = [];
        $totalAllocated = 0;
        for ($i = 0; $i < $remaining + 1; $i++) {
            if ($i === $remaining) {
                // last segment gets whatever is left
                $gaps[] = $available - $totalAllocated;
            } else {
                // allocate a random portion of remaining cells
                $maxShare = (int)(($available - $totalAllocated) * 0.6);
                $minShare = max(1, (int)(($available - $totalAllocated) / ($remaining + 1 - $i) * 0.3));
                $share = mt_rand($minShare, max($minShare, $maxShare));
                $gaps[] = $share;
                $totalAllocated += $share;
            }
        }

        // Convert gaps to cumulative positions
        $pos = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $pos += $gaps[$i];
            $waypoints[] = min($total - 2, max(1, $pos));
        }

        $waypoints[] = $total - 1; // last waypoint always at end

        // Apply jitter to interior waypoints (more aggressive for harder difficulties)
        $jitterChance = [30, 55, 75][$diffIndex];
        $jitterAmount = max(1, (int)($total * [0.08, 0.12, 0.18][$diffIndex]));
        for ($i = 1; $i < count($waypoints) - 1; $i++) {
            if (mt_rand(0, 100) < $jitterChance) {
                $prev = $waypoints[$i - 1];
                $next = $waypoints[$i + 1];
                $minPos = $prev + 1;
                $maxPos = $next - 1;
                $offset = mt_rand(-$jitterAmount, $jitterAmount);
                $waypoints[$i] = max($minPos, min($maxPos, $waypoints[$i] + $offset));
            }
        }

        $waypoints = array_unique($waypoints);
        sort($waypoints);

        return array_values(array_slice($waypoints, 0, $numWaypoints));
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
