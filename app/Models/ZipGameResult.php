<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipGameResult extends Model
{
    protected $fillable = [
        'user_id',
        'puzzle_id',
        'completion_time_seconds',
        'path_submitted',
        'is_correct',
        'completed_at',
    ];

    protected $casts = [
        'path_submitted' => 'array',
        'completed_at' => 'datetime',
        'is_correct' => 'boolean',
    ];

    public function puzzle()
    {
        return $this->belongsTo(ZipPuzzle::class, 'puzzle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
