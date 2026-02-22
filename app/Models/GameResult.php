<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GameResult extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('GameResult')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'team_name' => 'array',
    ];

    public function getImageAttribute($value)
    {
        if (isset($value) && !empty($value)) {
            $imageUrl = getImageUrlIfExists($value, Config::get('general.image_path.game_result.image'));
            return $imageUrl ?: '';
        }

        return '';
    }

    public function scopeLoadRelationships($query)
    {
        return $query->with([
            'caption',
            'caption.nativePlace',
            'wiseCaption',
            'wiseCaption.nativePlace',
            'manOfTheMatch',
            'manOfTheMatch.nativePlace',
            'members',
            'members.nativePlace',
        ])->orderBy('rank');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id')->withTrashed();
    }

    public function caption(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'caption_id', 'id')->withTrashed();
    }

    public function wiseCaption(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'wise_caption_id', 'id')->withTrashed();
    }

    public function manOfTheMatch(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'man_of_the_match_id', 'id')->withTrashed();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class);
    }
}
