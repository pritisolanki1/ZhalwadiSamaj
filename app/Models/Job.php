<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Job extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Job')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'job_description' => 'array',
    ];

    public static function GetAll($id = '')
    {
        $iRes = self::with([
            'member:id,name,phone,avatar,head_of_the_family_id,native_place_id,status',
            'member.nativePlace',
        ])->Select(
            'id',
            'member_id',
            'title',
            'job_description',
            'avatar',
            'city',
            'status',
            'created_at',
            'updated_at'
        );
        if (!empty($id)) {
            return $iRes->find($id);
        } else {
            $iRes = $iRes->orderBy('created_at', 'desc')->get(); //->groupBy('city');
        }

        // return count($iRes) > 0 ? [$iRes] : $iRes;
        return $iRes;
    }

    public function getStatusAttribute($value)
    {
        if ($value == '1') {
            $value = 'Active';
        } elseif ($value == '2') {
            $value = 'Draft';
        } elseif ($value == '0') {
            $value = 'Block';
        }

        return $value;
    }

    public function setStatusAttribute($value): string
    {
        if ($value == 'Active') {
            $value = 1;
        } elseif ($value == 'Draft') {
            $value = 2;
        } elseif ($value == 'Block') {
            $value = 0;
        }

        return $this->attributes['status'] = strtolower($value);
    }

    public function getAvatarAttribute($value)
    {
        if (isset($value) && !empty($value)) {
            $contains = Str::contains($value, 'image/0/0/image/Job/avatar/');
            if ($contains) {
                return $value;
            } else {
                $imageUrl = getImageUrlIfExists($value, Config::get('general.image_path.job.avatar'));
                return $imageUrl ?: '';
            }
        }

        return '';
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
