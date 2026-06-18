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

        $size = 6;
        $total = $size * $size;

        $solutionPath = [];
        for ($row = 0; $row < $size; $row++) {
            if ($row % 2 == 0) {
                for ($col = 0; $col < $size; $col++) {
                    $solutionPath[] = [$row, $col];
                }
            } else {
                for ($col = $size - 1; $col >= 0; $col--) {
                    $solutionPath[] = [$row, $col];
                }
            }
        }

        $numWaypoints = rand(6, 12);
        $waypointIndices = [];
        $waypointIndices[0] = 0;
        $step = max(1, (int) (($total - 1) / ($numWaypoints - 1)));
        for ($i = 1; $i < $numWaypoints; $i++) {
            $idx = min($i * $step, $total - 1) + rand(-1, 1);
            $idx = max(1, min($total - 1, $idx));
            if (!in_array($idx, $waypointIndices)) {
                $waypointIndices[] = $idx;
            }
        }
        sort($waypointIndices);

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
            'difficulty' => 'hard',
        ]);
    }
}
