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
}
