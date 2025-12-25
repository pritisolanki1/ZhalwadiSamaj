<?php

namespace App\Traits;

use App\Models\Announcement;
use App\Models\Donation;
use App\Models\GameResult;
use App\Models\Kuldevi;
use App\Models\MemberGallery;
use App\Models\NativePlace;
use App\Models\Zone;
use Awobaz\Compoships\Database\Eloquent\Relations\BelongsTo;
use Awobaz\Compoships\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait MemberRelations
{
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'member_id', 'id')->orderBy('created_at', 'desc');
    }

    public function headOfTheFamily(): BelongsTo
    {
        return $this->belongsTo(self::class)->withTrashed();
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class)->withTrashed();
    }

    public function kuldevi(): BelongsTo
    {
        return $this->belongsTo(Kuldevi::class)->withTrashed();
    }

    public function nativePlace(): BelongsTo
    {
        return $this->belongsTo(NativePlace::class)->withTrashed();
    }

    public function memberGalleries(): HasMany
    {
        return $this->hasMany(MemberGallery::class, 'member_id', 'id')->orderBy('created_at', 'desc');
    }

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class);
    }

    public function gameResults(): BelongsToMany
    {
        return $this->belongsToMany(GameResult::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'father_id')->loadRelation();
    }

    public function spouse(): HasMany
    {
        // below has-many used because Man who have multiple wife.
        return $this->hasMany(self::class, 'relation_id')->loadRelation();
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, [
            'father_id',
            'mother_id',
        ], [
            'relation_id',
            'id',
        ])->orderBy('birth_date', 'ASC')->loadRelation();
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('spouseRecursive');
    }

    public function spouseRecursive(): HasMany
    {
        return $this->spouse()->with('childrenRecursive');
    }

    public function childMemberRecursive(): HasMany
    {
        if ($this->gender == 'Male') {
            return $this->spouseRecursive();
        }

        return $this->childrenRecursive();
    }

    public function parentRecursive(): BelongsTo
    {
        return $this->parent()->with([
            'spouseRecursive',
            'parentRecursive',
        ]);
    }
}
