<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Team extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Team')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'team_type' => 'array',
    ];

    public static function GetAll($id = '')
    {
        $iRes = self::with([
            'member:id,name,avatar,head_of_the_family_id',
        ]);
        if (!empty($id)) {
            return $iRes->find($id);
        } else {
            $iRes = $iRes->get()->groupBy('team_type.en');
        }

        return count($iRes) > 0 ? [$iRes] : $iRes;
    }

    public function getAvatarAttribute($value)
    {
        if (isset($value) && !empty($value)) {
            $value = url('/image/0/0/' . Config::get('general.image_path.team.avatar') . $value);
        }

        return singeValue($value);
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

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id')->withTrashed();
    }
}
