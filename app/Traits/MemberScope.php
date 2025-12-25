<?php

namespace App\Traits;

use App\Filters\MemberFilters;
use Illuminate\Database\Eloquent\Builder;

trait MemberScope
{
    public function scopeFilter($query, MemberFilters $filters): Builder
    {
        return $filters->apply($query);
    }

    public function scopeLoadRelation($query): Builder
    {
        return $query->with([
            'donations:id,member_id,donations_type,amount,date,transition_id,transition,transition_status,status',
            'memberGalleries:id,member_id,images,videos,created_at',
            'zone:id,name',
            'kuldevi:id,name',
            'nativePlace:id,native',
        ]);
    }

    public function scopeHeadOfFamily($query, $memberId)
    {
        return $query->where('head_of_the_family_id', $memberId);
    }

    public function scopeFather($query, $memberId)
    {
        return $query->where('father_id', $memberId);
    }

    public function scopeMother($query, $value)
    {
        return $query->where('mother_id', $value);
    }

    public function scopeNullHeadOfTheFamily($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('head_of_the_family_id')
                ->orWhere('head_of_the_family_id', '<>', '');
        });
    }

    public function scope($query, $value)
    {
        return $query;
    }
}
