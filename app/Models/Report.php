<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Report extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Report')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function reported_user(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'report_user_id', 'id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getImageAttribute($value): string|UrlGenerator|Application
    {
        if (isset($value) && !empty($value)) {
            return getImageUrlIfExists($value, Config::get('general.image_path.report.image'));
        }

        return '';
    }
}
