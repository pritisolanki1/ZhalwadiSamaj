<?php

namespace App\Models;

use App\Traits\BusinessAttributes;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Business extends Model
{
    use BusinessAttributes, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Business')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'name'       => 'array',
        'address'    => 'array',
        'phone'      => 'array',
        'email'      => 'array',
        'website'    => 'array',
        'about'      => 'array',
        'partner_id' => 'array',
        'logo'       => 'array',
        'slider'     => 'array',
        'gallery'    => 'array',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'business_id');
    }
}
