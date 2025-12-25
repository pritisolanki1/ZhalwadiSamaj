<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Donation extends Model
{
    use LogsActivity, SoftDeletes;

    public const DONATION_TYPE_CASH = 'cash';
    public const DONATION_TYPE_ONLINE = 'online';
    public const DONATION_TYPE_PRODUCT = 'product';

    public const DONATION_TYPES = [
        self::DONATION_TYPE_CASH,
        self::DONATION_TYPE_ONLINE,
        self::DONATION_TYPE_PRODUCT,
    ];

    public const TRANSITION_STATUS_DONE = 'done';
    public const TRANSITION_STATUS_PENDING = 'pending';
    public const TRANSITION_STATUS_REJECT = 'reject';

    public const TRANSITION_STATUSES = [
        self::TRANSITION_STATUS_DONE,
        self::TRANSITION_STATUS_PENDING,
        self::TRANSITION_STATUS_REJECT,
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_BLOCK = 'block';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DRAFT,
        self::STATUS_BLOCK,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Donation')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'transition' => 'array',
    ];

    public function scopeLoadRelationships($query)
    {
        return $query->with([
            'member',
            'member.nativePlace',
        ]);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id')->withTrashed();
    }
}
