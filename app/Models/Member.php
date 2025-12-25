<?php

namespace App\Models;

use App\Traits\MemberAttributes;
use App\Traits\MemberRelations;
use App\Traits\MemberScope;
use Awobaz\Compoships\Compoships;
use GoldSpecDigital\LaravelEloquentUUID\Foundation\Auth\User as AuthUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class Member extends AuthUser
{
    use Compoships, HasApiTokens, HasFactory, HasRoles, LogsActivity, MemberAttributes, MemberRelations, MemberScope, Notifiable, SoftDeletes;

    protected string $guard_name = 'member';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Member')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($member) {
            $member->update([
                'email' => $member->email . '#' . Str::random(5),
                'phone' => $member->phone . '#' . Str::random(5),
                'unique_number' => $member->unique_number . '#' . Str::random(5),
            ]);
        });
    }

    protected $casts = [
        'name'              => 'array',
        // 'phone'           => 'array' ,
        // 'email'           => 'array' ,
        'address'           => 'array',
        'occupation'        => 'array',
        'qualification'     => 'array',
        'profession'        => 'array',
        'profession_type'   => 'array',
        'work_address'      => 'array',
        'mosal'             => 'array',
        'mother_name'       => 'array',
        'father_name'       => 'array',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function findForPassport($username): AuthUser|self
    {
        return $this->where('phone', $username)->first();
    }

    public function syncDonation()
    {
        $this->total_donation = self::donations()->sum('amount');
        $this->save();

        return $this->total_donation;
    }
}
