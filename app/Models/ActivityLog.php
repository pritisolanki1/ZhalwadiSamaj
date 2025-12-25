<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id', 'id')->withTrashed();
    }

    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_id', 'id')->withTrashed();
    }

    public function announcemet(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'subject_id', 'id')->withTrashed();
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'subject_id', 'id')->withTrashed();
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'subject_id', 'id')->withTrashed();
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class, 'subject_id', 'id')->withTrashed();
    }

    public function galleryImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'subject_id', 'id')->withTrashed();
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class, 'subject_id', 'id')->withTrashed();
    }

    public function gameResult(): BelongsTo
    {
        return $this->belongsTo(GameResult::class, 'subject_id', 'id')->withTrashed();
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'subject_id', 'id')->withTrashed();
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'subject_id', 'id')->withTrashed();
    }

    public function kuldevi(): BelongsTo
    {
        return $this->belongsTo(Kuldevi::class, 'subject_id', 'id')->withTrashed();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'subject_id', 'id')->withTrashed();
    }

    public function memberGallery(): BelongsTo
    {
        return $this->belongsTo(MemberGallery::class, 'subject_id', 'id')->withTrashed();
    }

    public function nativePlaces(): BelongsTo
    {
        return $this->belongsTo(NativePlace::class, 'subject_id', 'id')->withTrashed();
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'subject_id', 'id')->withTrashed();
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class, 'subject_id', 'id')->withTrashed();
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'subject_id', 'id')->withTrashed();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'subject_id', 'id')->withTrashed();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }
}
