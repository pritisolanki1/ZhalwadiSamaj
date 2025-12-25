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
        if ($type == 'father') {
            $childMembers = $this->getChildMembers($this->request->member_id);
            $this->builder
                ->where('members.gender', 'Male')
                ->whereNull('members.relation_id')
                ->whereNotNull('members2.id')
                ->whereNotIn('members.id', $childMembers)
                ->leftJoin('members AS members2', function ($join) {
                    $join->on('members2.relation_id', '=', 'members.id');
                })
                ->select('members.*');
        } elseif ($type == 'mother') {
            $this->builder
                ->where('members.gender', 'Female')
                ->whereNotNull('relation_id');
        } elseif ($type == 'husband') {
            $childMembers = $this->getChildMembers($this->request->member_id);
            $this->builder
                ->where('members.gender', 'Male')
                ->whereNull('members.relation_id')
                ->whereNotNull('members2.id')
                ->whereNotIn('members.id', $childMembers)
                ->leftJoin('members AS members2', function ($join) {
                    $join->on('members2.relation_id', '!=', 'members.id');
                })
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
