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

        $size = ($dayOfMonth % 2 === 0) ? 8 : 7;
        $difficulty = 'hard';
        $numWaypoints = rand(20, 28);

        $total = $size * $size;
        if ($numWaypoints > $total) $numWaypoints = $total;

        $pathPattern = $dayOfMonth % 4;
        switch ($pathPattern) {
            case 0: $solutionPath = self::horizontalSnake($size, true); break;
            case 1: $solutionPath = self::verticalSnake($size, true); break;
            case 2: $solutionPath = self::horizontalSnake($size, false); break;
            default: $solutionPath = self::verticalSnake($size, false); break;
        }

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

        return self::create([
            'grid_size' => $size,
            'grid_numbers' => $gridNumbers,
            'solution_path' => $solutionPath,
            'puzzle_date' => $date,
            'difficulty' => $difficulty,
        ]);
    }

    private static function horizontalSnake($size, $startLeft = true)
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

    private static function verticalSnake($size, $startTop = true)
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
