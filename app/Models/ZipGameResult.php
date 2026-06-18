<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZipGameResult extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'path_taken'        => 'array',
        'completed_at'      => 'datetime',
    ];

    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(ZipPuzzle::class, 'puzzle_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id');
    }
}
