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

        $dayOfWeek = (int) $date->format('N');
        $dayOfMonth = (int) $date->format('j');

        $diffIndex = ($dayOfWeek + $dayOfMonth) % 3;
        if ($diffIndex === 0) {
            $difficulty = 'easy';
            $size = 6;
            $numWaypoints = rand(8, 11);
        } elseif ($diffIndex === 1) {
            $difficulty = 'medium';
            $size = 7;
            $numWaypoints = rand(14, 18);
        } else {
            $difficulty = 'hard';
            $size = 8;
            $numWaypoints = rand(20, 28);
        }

        $total = $size * $size;
        if ($numWaypoints > $total) $numWaypoints = $total;

        $pattern = ($diffIndex + $dayOfMonth) % 2;
        $solutionPath = $pattern === 0
            ? self::horizontalSnake($size)
            : self::verticalSnake($size);

        $waypointIndices = [];
        $waypointIndices[0] = 0;
        $waypointIndices[] = $total - 1;
        $remaining = $numWaypoints - 2;
        if ($remaining > 0) {
            $candidates = range(1, $total - 2);
            shuffle($candidates);
            $picked = array_slice($candidates, 0, $remaining);
            $waypointIndices = array_merge($waypointIndices, $picked);
        }
        sort($waypointIndices);
        $waypointIndices = array_values(array_unique($waypointIndices));

        $gridNumbers = [];
        foreach ($waypointIndices as $num => $idx) {
            $pos = $solutionPath[$idx];
            $gridNumbers[] = [
                'row' => $pos[0],
                'col' => $pos[1],
                'number' => $num + 1,
            ];
        }

        $finalWaypoints = count($gridNumbers);
        if ($finalWaypoints >= 20) $difficulty = 'hard';
        elseif ($finalWaypoints >= 12) $difficulty = 'medium';
        else $difficulty = 'easy';

        return self::create([
            'grid_size' => $size,
            'grid_numbers' => $gridNumbers,
            'solution_path' => $solutionPath,
            'puzzle_date' => $date,
            'difficulty' => $difficulty,
        ]);
    }

    private static function horizontalSnake($size)
    {
        $path = [];
        for ($row = 0; $row < $size; $row++) {
            if ($row % 2 === 0) {
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

    private static function verticalSnake($size)
    {
        $path = [];
        for ($col = 0; $col < $size; $col++) {
            if ($col % 2 === 0) {
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
