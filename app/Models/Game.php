<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Game extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Game')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'game_name' => 'array',
    ];

    public static function GetAll($id = '')
    {
        $iRes = self::with([
            'gameResults',
            'gameResults' => function ($query) {
                $query->with([
                    'caption',
                    'caption.nativePlace',
                    'wiseCaption',
                    'wiseCaption.nativePlace',
                    'manOfTheMatch',
                    'manOfTheMatch.nativePlace',
                    'members',
                    'members.nativePlace',
                ])->orderBy('rank', 'asc');
            },
        ]);

        if (!empty($id)) {
            return $iRes->find($id);
        } else {
            $iRes = $iRes->orderBy('year')->get();
        }

        return $iRes;
    }

    public function gameResults(): HasMany
    {
        return $this->hasMany(GameResult::class, 'game_id');
    }
}
