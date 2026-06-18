<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZipPuzzle extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'grid_data'     => 'array',
        'solution_path' => 'array',
        'puzzle_date'   => 'date',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(ZipGameResult::class, 'puzzle_id');
    }
}
