<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'force_update' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'latest_version'            => '1.2',
            'minimum_supported_version' => '1.2',
            'force_update'              => false,
            'update_message'            => 'A new version is available. Please update to continue.',
            'play_store_url'            => 'https://play.google.com/store/apps/details?id=com.zalawadi.app',
        ]);
    }
}
