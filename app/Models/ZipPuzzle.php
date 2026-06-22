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

        // Rotate difficulty: easy/medium/hard
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

        // Alternate between horizontal and vertical snake for variety
        $patternIndex = $dayOfMonth % 4;
        switch ($patternIndex) {
            case 0: $solutionPath = self::horizontalSnake($size, true); break;
            case 1: $solutionPath = self::verticalSnake($size, true); break;
            case 2: $solutionPath = self::horizontalSnake($size, false); break;
            default: $solutionPath = self::verticalSnake($size, false); break;
        }

        $waypointIndices = self::pickWaypoints($total, $numWaypoints);

        $gridNumbers = [];
        foreach ($waypointIndices as $num => $idx) {
            $pos = $solutionPath[$idx];
            $gridNumbers[] = [
                'row' => $pos[0],
                'col' => $pos[1],
                'number' => $num + 1,
            ];
        }

        return self::create([
            'grid_size' => $size,
            'grid_numbers' => $gridNumbers,
            'solution_path' => $solutionPath,
            'puzzle_date' => $date,
            'difficulty' => $difficulty,
        ]);
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
