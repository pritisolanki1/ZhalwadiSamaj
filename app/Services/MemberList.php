<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;

class MemberList
{
    protected mixed $headOfTheFamilyId;

    protected $members;

    protected $familyMemberIds;

    public function __construct($memberId = null)
    {
        if (!is_null($memberId)) {
            $member = Member::find($memberId);
            if ($member && $member->head_of_the_family_id != null && $member->head_of_the_family_id != '') {
                $this->headOfTheFamilyId = $member->head_of_the_family_id;
            } else {
                $this->headOfTheFamilyId = $memberId;
            }
        }
    }

    public function get(): Collection
    {
        $this->getMembers();

        //        $this->filter();
        return $this->members;
    }

    private function getMembers(): void
    {
        $this->members = Member::query()
            ->with([
                'donations',
                'memberGalleries',
                'zone:name',
                'kuldevi:name',
                'nativePlace:native',
                'spouseRecursive',
                'parentRecursive',
            ])
            ->whereNull('head_of_the_family_id')->when(!is_null($this->headOfTheFamilyId), function ($query) {
                $query->where('id', $this->headOfTheFamilyId);
            })
            ->orderBy('name_en')
            ->get();
    }

    private function filter(): void
    {
        $this->members = $this->members->map(function ($value) {
            $this->familyMemberIds = [];
            $this->familyMemberIds[] = $value->id;
            $value->relation_type = [
                'en' => 'Self',
                'gu' => 'પોતે',
            ];
            $this->checkWife($value->spouseRecursive);
            $this->checkFather($value->parentRecursive);

            return $value;
        });
    }

    private function checkWife($wifeObject): void
    {
        if ($wifeObject) {
            $wifeObject->each(function ($wife) {
                $this->familyMemberIds[] = $wife->id;
                $wife->relation_type = [
                    'en' => 'Wife',
                    'gu' => 'પત્ની',
                ];
                $this->checkSonDaughter($wife->childrenRecursive);
            });
        }
    }

    private function checkSonDaughter($sonDaughterObject): void
    {
        if ($sonDaughterObject) {
            $sonDaughterObject->each(function ($sonDaughter) {
                if ($sonDaughter->gender == 'Male') {
                    $this->familyMemberIds[] = $sonDaughter->id;
                    $sonDaughter->relation_type = [
                        'en' => 'Son',
                        'gu' => 'દીકરો',
                    ];
                    $this->checkDaughterInLaw($sonDaughter->spouseRecursive);
                } elseif ($sonDaughter->gender == 'Female') {
                    $this->familyMemberIds[] = $sonDaughter->id;
                    $sonDaughter->relation_type = [
                        'en' => 'Daughter',
                        'gu' => 'દીકરી',
                    ];
                }
            });
        }
    }

    private function checkDaughterInLaw($daughterInLawObject): void
    {
        if ($daughterInLawObject) {
            $daughterInLawObject->each(function ($daughterInLaw) {
                $this->familyMemberIds[] = $daughterInLaw->id;
                $daughterInLaw->relation_type = [
                    'en' => 'Daughter-in-law',
                    'gu' => 'પુત્રવધૂ',
                ];
                $this->checkGrandSonDaughter($daughterInLaw->childrenRecursive);
            });
        }
    }

    private function checkGrandSonDaughter($grandSonDaughterObject): void
    {
        if ($grandSonDaughterObject) {
            $grandSonDaughterObject->each(function ($grandSonDaughter) {
                if ($grandSonDaughter->gender == 'Male') {
                    $this->familyMemberIds[] = $grandSonDaughter->id;
                    $grandSonDaughter->relation_type = [
                        'en' => 'Grandson',
                        'gu' => 'પૌત્ર',
                    ];
                    $this->checkGrandDaughterInLaw($grandSonDaughter->spouseRecursive);
                } elseif ($grandSonDaughter->gender == 'Female') {
                    $this->familyMemberIds[] = $grandSonDaughter->id;
                    $grandSonDaughter->relation_type = [
                        'en' => 'Granddaughter',
                        'gu' => 'પૌત્રી',
                    ];
                }
            });
        }
    }

    private function checkGrandDaughterInLaw($grandDaughterInLawObject): void
    {
        if ($grandDaughterInLawObject) {
            $grandDaughterInLawObject->each(function ($grandDaughterInLaw) {
                $grandDaughterInLaw->relation_type = [
                    'en' => 'Grand-daughter-in-law',
                    'gu' => 'વહુ-વહુ',
                ];
                $this->familyMemberIds[] = $grandDaughterInLaw->id;
                $this->checkGreatGrandSonDaughter($grandDaughterInLaw->childrenRecursive);
            });
        }
    }

    private function checkGreatGrandSonDaughter($greatGrandSonDaughterObject): void
    {
        if ($greatGrandSonDaughterObject) {
            $greatGrandSonDaughterObject->each(function ($greatGrandSonDaughter) {
                if ($greatGrandSonDaughter->gender == 'Male') {
                    $this->familyMemberIds[] = $greatGrandSonDaughter->id;
                    $greatGrandSonDaughter->relation_type = [
                        'en' => 'Great grandson',
                        'gu' => 'પ્રપૌત્ર',
                    ];
                    $this->checkGrateGrandDaughterInLaw($greatGrandSonDaughter->spouseRecursive);
                } elseif ($greatGrandSonDaughter->gender == 'Female') {
                    $this->familyMemberIds[] = $greatGrandSonDaughter->id;
                    $greatGrandSonDaughter->relation_type = [
                        'en' => 'Great granddaughter',
                        'gu' => 'પપૌત્રી',
                    ];
                }
            });
        }
    }

    private function checkGrateGrandDaughterInLaw($grateGrandDaughterInLawObject): void
    {
        if ($grateGrandDaughterInLawObject) {
            $grateGrandDaughterInLawObject->each(function ($grateGrandDaughterInLaw) {
                $this->familyMemberIds[] = $grateGrandDaughterInLaw->id;
                $grateGrandDaughterInLaw->relation_type = [
                    'en' => 'Great-grand-daughter-in-law',
                    'gu' => 'મોટી-મોટી-વહુ',
                ];
                $this->checkGreatGrateGrandSonDaughter($grateGrandDaughterInLaw->childrenRecursive);
            });
        }
    }

    private function checkGreatGrateGrandSonDaughter($greatGrateGrandSonDaughterObject): void
    {
        if ($greatGrateGrandSonDaughterObject) {
            $greatGrateGrandSonDaughterObject->each(function ($greatGrateGrandSonDaughter) {
                if ($greatGrateGrandSonDaughter->gender == 'Male') {
                    $this->familyMemberIds[] = $greatGrateGrandSonDaughter->id;
                    $greatGrateGrandSonDaughter->relation_type = [
                        'en' => 'Grate great grandson',
                        'gu' => 'પ્રપૌત્ર',
                    ];
                } elseif ($greatGrateGrandSonDaughter->gender == 'Female') {
                    $this->familyMemberIds[] = $greatGrateGrandSonDaughter->id;
                    $greatGrateGrandSonDaughter->relation_type = [
                        'en' => 'Grate great granddaughter',
                        'gu' => 'પપૌત્રી',
                    ];
                }
            });
        }
    }

    private function checkFather($parentObject): void
    {
        if ($parentObject) {
            $this->familyMemberIds[] = $parentObject->id;
            $parentObject->relation_type = [
                'en' => 'Father',
                'gu' => 'પિતા',
            ];
            $this->checkMother($parentObject->spouseRecursive);
            //            $this->checkGrandFather($parentObject->parentRecursive);
        }
    }

    private function checkMother($motherObject): void
    {
        if ($motherObject) {
            $motherObject->each(function ($mother) {
                $this->familyMemberIds[] = $mother->id;
                $mother->relation_type = [
                    'en' => 'Mother',
                    'gu' => 'માતા',
                ];
                $this->checkBrotherSister($mother->childrenRecursive);
            });
        }
    }

    private function checkBrotherSister($brotherSisterObject): void
    {
        if ($brotherSisterObject) {
            $brotherSisterObject->each(function ($brotherSister, $key) use ($brotherSisterObject) {
                if ($brotherSister->gender == 'Male') {
                    if (in_array($brotherSister->id, $this->familyMemberIds)) {
                        $brotherSisterObject->forget($key);
                    } else {
                        $this->familyMemberIds[] = $brotherSister->id;
                        $brotherSister->relation_type = [
                            'en' => 'Brother',
                            'gu' => 'ભાઈ',
                        ];
                        $this->checkSisterInLaw($brotherSister->spouseRecursive);
                    }
                } elseif ($brotherSister->gender == 'Female') {
                    $this->familyMemberIds[] = $brotherSister->id;
                    $brotherSister->relation_type = [
                        'en' => 'Sister',
                        'gu' => 'બહેન',
                    ];
                }
            });
        }
    }

    private function checkSisterInLaw($sisterInLawObject): void
    {
        if ($sisterInLawObject) {
            $sisterInLawObject->each(function ($sisterInLaw) {
                $this->familyMemberIds[] = $sisterInLaw->id;
                $sisterInLaw->relation_type = [
                    'en' => 'Sister-in-law',
                    'gu' => 'ભાભી',
                ];
                $this->checkNephewNiece($sisterInLaw->childrenRecursive);
            });
        }
    }

    private function checkNephewNiece($nephewNieceObject): void
    {
        if ($nephewNieceObject) {
            $nephewNieceObject->each(function ($nephewNiece, $key) use ($nephewNieceObject) {
                if ($nephewNiece->gender == 'Male') {
                    if (in_array($nephewNiece->id, $this->familyMemberIds)) {
                        $nephewNieceObject->forget($key);
                    } else {
                        $this->familyMemberIds[] = $nephewNiece->id;
                        $nephewNiece->relation_type = [
                            'en' => 'Nephew',
                            'gu' => 'ભત્રીજા',
                        ];
                        //                        TODO:Work pending from nephewInLaw
                        //                        $this->checkNephewInLaw($nephewNiece->spouseRecursive);
                    }
                } elseif ($nephewNiece->gender == 'Female') {
                    $this->familyMemberIds[] = $nephewNiece->id;
                    $nephewNiece->relation_type = [
                        'en' => 'Niece',
                        'gu' => 'ભત્રીજી',
                    ];
                }
            });
        }
    }
}
