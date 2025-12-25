<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Announcement extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Announcement')->logOnlyDirty()->logUnguarded()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getImageAttribute($value): string|UrlGenerator|Application
    {
        $image = '';
        if (isset($value) && !empty($value)) {
            $image = url('/image/0/0/' . Config::get('general.image_path.announcement.image') . $value);
        }

        return $image;
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class);
    }
}
