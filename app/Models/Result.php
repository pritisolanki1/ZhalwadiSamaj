<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Result extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Result')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'percentage' => 'string',
        'percentile' => 'string',
    ];

    public static function GetAll($id = '')
    {
        $iRes = self::with([
            'member:id,name,avatar,head_of_the_family_id,native_place_id',
            'member.nativePlace:id,native',
        ])->orderBy('rank')->orderBy('class');

        if (!empty($id)) {
            return $iRes->find($id);
        } else {
            $iRes = $iRes->get()->groupBy('medium');
        }

        return count($iRes) > 0 ? [$iRes] : $iRes;
    }

    public static function GetAllResultYear(): array|Collection
    {
        $iRes = self::distinct()->orderBy('year', 'asc')->select('year', 'type')->get()->groupBy('type');

        return count($iRes) > 0 ? [$iRes] : $iRes;
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
        return $this->belongsTo(Member::class, 'member_id', 'id')->withTrashed();
    }
}
