<?php

namespace App\Filters;

use App\Models\Member;

class MemberFilters extends Filters
{
    protected $filters = ['head_of_the_family_id', 'relation_id', 'search_type'];

    protected function head_of_the_family_id($headOfTheFamilyMemberId)
    {
        $this->builder->where(function ($q) use ($headOfTheFamilyMemberId) {
            $q->where('members.head_of_the_family_id', $headOfTheFamilyMemberId)
                ->orWhere('members.id', $headOfTheFamilyMemberId);
        });
    }

    protected function relation_id($relationId)
    {
        $this->builder->whereNotNull('members.relation_id')->where('members.relation_id', $relationId);
    }

    protected function search_type($type)
    {
        //  Phase-1 note: members last saved by old app builds carry ''
        //  instead of NULL in relationship columns, so every branch below
        //  must treat NULL and '' alike.
        if ($type == 'father') {
            $childMembers = $this->getChildMembers($this->request->member_id);
            $this->builder
                ->where('members.gender', 'Male')
                ->where(function ($q) {
                    $q->whereNull('members.relation_id')->orWhere('members.relation_id', '');
                })
                ->whereNotIn('members.id', $childMembers)
                ->select('members.*');
        } elseif ($type == 'mother') {
            if ($this->request->filled('relation_id')) {
                // Wife lookup (getwifeCorrospondToHusband): relation_id filter handles matching.
                $this->builder->where('members.gender', 'Female');
            } else {
                // Independent mother picker: show eligible females, exclude self/descendants.
                $childMembers = $this->getChildMembers($this->request->member_id);
                $this->builder
                    ->where('members.gender', 'Female')
                    ->where(function ($q) {
                        $q->whereNull('members.relation_id')->orWhere('members.relation_id', '');
                    })
                    ->whereNotIn('members.id', $childMembers)
                    ->select('members.*');
            }
        } elseif ($type == 'husband') {
            $childMembers = $this->getChildMembers($this->request->member_id);
            $this->builder
                ->where('members.gender', 'Male')
                ->where(function ($q) {
                    $q->whereNull('members.relation_id')->orWhere('members.relation_id', '');
                })
                ->whereNotIn('members.id', $childMembers)
                ->select('members.*');
        }
    }

    private function getChildMembers($memberId): array
    {
        $loadMembers = optional(Member::find($memberId))->childMemberRecursive();

        $members = [];
        if ($loadMembers) {
            $members = $this->getChildMembersRecursive($loadMembers->get());
            $members[] = $memberId;
        }

        return $members;
    }

    private function getChildMembersRecursive($loadMembers): array
    {
        $members = [];
        foreach ($loadMembers as $loadMember) {
            $members[] = $loadMember->id;
            if ($loadMember->spouseRecursive) {
                $spouseMember = $this->getChildMembersRecursive($loadMember->spouseRecursive);
                $members = array_merge($members, $spouseMember);
            }

            if ($loadMember->childrenRecursive) {
                $childrenMember = $this->getChildMembersRecursive($loadMember->childrenRecursive);
                $members = array_merge($members, $childrenMember);
            }
        }

        return $members;
    }
}
