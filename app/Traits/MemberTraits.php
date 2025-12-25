<?php

namespace App\Traits;

use App\Models\Committee;
use App\Models\Donation;
use App\Models\Game;
use App\Models\GameResult;
use App\Models\Job;
use App\Models\Member;
use App\Models\MemberGallery;
use App\Models\Result;
use App\Models\RoleUser;
use App\Models\Team;
use App\Models\UserForgotPasswordToken;
use App\Services\MemberList;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

trait MemberTraits
{
    public function getMember($member_id = ''): \Illuminate\Support\Collection
    {
        $iWhere = ['head_of_the_family_id' => null];
        if (!empty($member_id)) {
            $head_member_data = Member::find($member_id);
            if ($head_member_data != null && $head_member_data->head_of_the_family_id != null && $head_member_data->head_of_the_family_id != '') {
                $head_member_id = $head_member_data->head_of_the_family_id;
            } else {
                $head_member_id = $member_id;
            }
            $iWhere['id'] = $head_member_id;
        }

        return Member::loadRelation()->where($iWhere)->orderBy('name_en')->get()->map(function ($value) {
            if ($value->head_of_the_family_id == null) {
                $value->relation_type = [
                    'en' => 'Self',
                    'gu' => 'પોતે',
                ];
            }

            return $value;
        })->map(function ($value) use ($member_id) {
            if (empty($member_id)) {
                return $value;
            }

            $head_of_family_id = $value->id;
            $head_of_family_father_id = $value->father_id;
            $head_of_family_mother_id = $value->mother_id;
            $head_of_family_grand_father_id = null;
            $head_of_family_grand_mother_id = null;
            $head_of_family_wife_id = '';
            //  Get Member List
            $iMemberList = Member::loadRelation()->where('head_of_the_family_id', $head_of_family_id)
                // ->orderBy("name", "asc")
                ->orderBy('birth_date', 'ASC')
                // ->select('id', 'father_id', 'mother_id', 'relation_id', 'name', 'native_place_id', 'head_of_the_family_id', 'gender')
                ->get();
            $iFamily = [];

            //  Set Wife
            foreach ($iMemberList as $iKey1 => $iMember1) {
                if ($iMember1->relation_id == $head_of_family_id && $iMember1->gender == 'Female') {
                    $head_of_family_wife_id = $iMember1->id;
                    $iMember1->relation_type = [
                        'en' => 'Wife',
                        'gu' => 'પત્ની',
                    ];
                    $iMember1Children = [];

                    //  Set Son And Daughter
                    foreach ($iMemberList as $iKey2 => $iMember2) {
                        if ($iMember2->father_id == $head_of_family_id && $iMember2->mother_id == $head_of_family_wife_id) {
                            //  Son Data
                            if ($iMember2->gender == 'Male') {
                                $iMember2->relation_type = [
                                    'en' => 'Son',
                                    'gu' => 'દીકરો',
                                ];
                                $head_of_family_Son_id = $iMember2->id;
                                $head_of_family_Daughter_in_law_id = '';
                                $iMember1Children[] = $iMember2;

                                //  Set Daughter in Law
                                foreach ($iMemberList as $iKey3 => $iMember3) {
                                    if ($iMember3->relation_id == $head_of_family_Son_id && $iMember3->gender == 'Female') {
                                        $head_of_family_Daughter_in_law_id = $iMember3->id;
                                        $iMember3->relation_type = [
                                            'en' => 'Daughter-in-law',
                                            'gu' => 'પુત્રવધૂ',
                                        ];
                                        $iMember3Children = [];

                                        //  Set Grand Son And Grand Daughter
                                        foreach ($iMemberList as $iKey4 => $iMember4) {
                                            if ($iMember4->father_id == $head_of_family_Son_id && $iMember4->mother_id == $head_of_family_Daughter_in_law_id) {
                                                //  Grand Son Data
                                                if ($iMember4->gender == 'Male') {
                                                    $iMember4->relation_type = [
                                                        'en' => 'Grandson',
                                                        'gu' => 'પૌત્ર',
                                                    ];
                                                    $head_of_family_Grand_Son_id = $iMember4->id;
                                                    $head_of_family_Grand_Daughter_in_law_id = '';
                                                    $iMember3Children[] = $iMember4;

                                                    //  Set Grand Daughter in Law
                                                    foreach ($iMemberList as $iKey5 => $iMember5) {
                                                        if ($iMember5->relation_id == $head_of_family_Grand_Son_id && $iMember5->gender == 'Female') {
                                                            $head_of_family_Grand_Daughter_in_law_id = $iMember5->id;
                                                            $iMember5->relation_type = [
                                                                'en' => 'Grand-daughter-in-law',
                                                                'gu' => 'વહુ-વહુ',
                                                            ];
                                                            $iMember5Children = [];
                                                            //  Set GreatGrand Son And GreatGrand Daughter
                                                            foreach ($iMemberList as $iKey6 => $iMember6) {
                                                                if ($iMember6->father_id == $head_of_family_Grand_Son_id && $iMember6->mother_id == $head_of_family_Grand_Daughter_in_law_id) {
                                                                    //  GreatGrand Son Data
                                                                    if ($iMember6->gender == 'Male') {
                                                                        $iMember6->relation_type = [
                                                                            'en' => 'Great grandson',
                                                                            'gu' => 'પ્રપૌત્ર',
                                                                        ];
                                                                        $head_of_family_Great_Grand_Son_id = $iMember6->id;
                                                                        $head_of_family_Great_Grand_Daughter_in_law_id = '';
                                                                        $iMember5Children[] = $iMember6;

                                                                        //  Set GreatGrand Daughter in Law
                                                                        foreach ($iMemberList as $iKey7 => $iMember7) {
                                                                            if ($iMember7->relation_id == $head_of_family_Great_Grand_Son_id && $iMember7->gender == 'Female') {
                                                                                $head_of_family_Great_Grand_Daughter_in_law_id = $iMember7->id;
                                                                                $iMember7->relation_type = [
                                                                                    'en' => 'Great-grand-daughter-in-law',
                                                                                    'gu' => 'મોટી-મોટી-વહુ',
                                                                                ];
                                                                                $iMember5Children[] = $iMember7;
                                                                                unset($iMemberList[$iKey7]);
                                                                            }
                                                                        }
                                                                        unset($iMemberList[$iKey6]);
                                                                    } //  GreatGrand Daughter Data
                                                                    else {
                                                                        if ($iMember6->gender == 'Female') {
                                                                            $iMember6->relation_type = [
                                                                                'en' => 'Great granddaughter',
                                                                                'gu' => 'પપૌત્રી',
                                                                            ];
                                                                            $iMember5Children[] = $iMember6;
                                                                            unset($iMemberList[$iKey6]);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            $iMember5->children = $iMember5Children;
                                                            $iMember3Children[] = $iMember5;
                                                            unset($iMemberList[$iKey5]);
                                                        }
                                                    }
                                                    unset($iMemberList[$iKey4]);
                                                } //  Grand Daughter Data
                                                else {
                                                    if ($iMember4->gender == 'Female') {
                                                        $iMember4->relation_type = [
                                                            'en' => 'Granddaughter',
                                                            'gu' => 'પૌત્રી',
                                                        ];
                                                        $iMember3Children[] = $iMember4;
                                                        unset($iMemberList[$iKey4]);
                                                    }
                                                }
                                            }
                                        }
                                        $iMember3->children = $iMember3Children;
                                        $iMember1Children[] = $iMember3;
                                        unset($iMemberList[$iKey3]);
                                    }
                                }
                                unset($iMemberList[$iKey2]);
                            } //  Daughter Data
                            else {
                                if ($iMember2->gender == 'Female') {
                                    $iMember2->relation_type = [
                                        'en' => 'Daughter',
                                        'gu' => 'દીકરી',
                                    ];
                                    $iMember1Children[] = $iMember2;
                                    unset($iMemberList[$iKey2]);
                                }
                            }
                        }
                    }

                    $iMember1->children = $iMember1Children;

                    $iFamily[] = $iMember1;
                    unset($iMemberList[$iKey1]);
                }
            }

            //  Set Father
            if ($head_of_family_father_id !== null) {
                foreach ($iMemberList as $iKey11 => $iMember11) {
                    if ($iMember11->id == $head_of_family_father_id && $iMember11->gender == 'Male') {
                        $iMember11->relation_type = [
                            'en' => 'Father',
                            'gu' => 'પિતા',
                        ];
                        $head_of_family_grand_father_id = $iMember11->father_id;
                        $head_of_family_grand_mother_id = $iMember11->mother_id;
                        $iFamily[] = $iMember11;
                        unset($iMemberList[$iKey11]);
                        // dd($iMember11->id);
                    }
                }
            }

            //  Set Mother
            if ($head_of_family_mother_id !== null) {
                foreach ($iMemberList as $iKey111 => $iMember111) {
                    if ($iMember111->id == $head_of_family_mother_id && $iMember111->gender == 'Female') {
                        $iMember111->relation_type = [
                            'en' => 'Mother',
                            'gu' => 'માતા',
                        ];
                        $iMember111Children = [];

                        //  Set Son And Daughter
                        foreach ($iMemberList as $iKey222 => $iMember222) {
                            if ($iMember222->father_id == $head_of_family_father_id && $iMember222->mother_id == $head_of_family_mother_id && $iMember222->id !== $head_of_family_id) {
                                //  Son Data
                                if ($iMember222->gender == 'Male') {
                                    $iMember222->relation_type = [
                                        'en' => 'Brother',
                                        'gu' => 'ભાઈ',
                                    ];
                                    $head_of_family_Son_id = $iMember222->id;
                                    $head_of_family_Daughter_in_law_id = '';
                                    $iMember111Children[] = $iMember222;

                                    //  Set Daughter in Law
                                    foreach ($iMemberList as $iKey333 => $iMember333) {
                                        if ($iMember333->relation_id == $head_of_family_Son_id && $iMember333->gender == 'Female') {
                                            $head_of_family_Daughter_in_law_id = $iMember333->id;
                                            $iMember333->relation_type = [
                                                'en' => 'Sister-in-law',
                                                'gu' => 'ભાભી',
                                            ];
                                            $iMember333Children = [];

                                            //  Set Grand Son And Grand Daughter
                                            foreach ($iMemberList as $iKey444 => $iMember444) {
                                                if ($iMember444->father_id == $head_of_family_Son_id && $iMember444->mother_id == $head_of_family_Daughter_in_law_id) {
                                                    //  Grand Son Data
                                                    if ($iMember444->gender == 'Male') {
                                                        $iMember444->relation_type = [
                                                            'en' => 'Nephew',
                                                            'gu' => 'ભત્રીજા',
                                                        ];
                                                        $head_of_family_Grand_Son_id = $iMember444->id;
                                                        $head_of_family_Grand_Daughter_in_law_id = '';
                                                        $iMember333Children[] = $iMember444;

                                                        //  Set Grand Daughter in Law
                                                        foreach ($iMemberList as $iKey555 => $iMember555) {
                                                            if ($iMember555->relation_id == $head_of_family_Grand_Son_id && $iMember555->gender == 'Female') {
                                                                $head_of_family_Grand_Daughter_in_law_id = $iMember555->id;
                                                                $iMember555->relation_type = [
                                                                    'en' => 'Nephew-in-law',
                                                                    'gu' => 'ભાણેજ',
                                                                ];
                                                                $iMember555Children = [];
                                                                //  Set GreatGrand Son And GreatGrand Daughter
                                                                foreach ($iMemberList as $iKey666 => $iMember666) {
                                                                    if ($iMember666->father_id == $head_of_family_Grand_Son_id && $iMember666->mother_id == $head_of_family_Grand_Daughter_in_law_id) {
                                                                        //  GreatGrand Son Data
                                                                        if ($iMember666->gender == 'Male') {
                                                                            $iMember666->relation_type = [
                                                                                'en' => 'Grandson',
                                                                                'gu' => 'પૌત્ર',
                                                                            ];
                                                                            $head_of_family_Great_Grand_Son_id = $iMember666->id;
                                                                            $head_of_family_Great_Grand_Daughter_in_law_id = '';
                                                                            $iMember555Children[] = $iMember666;

                                                                            //  Set GreatGrand Daughter in Law
                                                                            foreach ($iMemberList as $iKey777 => $iMember777) {
                                                                                if ($iMember777->relation_id == $head_of_family_Great_Grand_Son_id && $iMember777->gender == 'Female') {
                                                                                    $head_of_family_Great_Grand_Daughter_in_law_id = $iMember777->id;
                                                                                    $iMember777->relation_type = [
                                                                                        'en' => 'Grand-daughter-in-law',
                                                                                        'gu' => 'પૌત્રી-વહુ',
                                                                                    ];
                                                                                    $iMember555Children[] = $iMember777;
                                                                                    unset($iMemberList[$iKey777]);
                                                                                }
                                                                            }
                                                                            unset($iMemberList[$iKey666]);
                                                                        } //  GreatGrand Daughter Data
                                                                        else {
                                                                            if ($iMember666->gender == 'Female') {
                                                                                $iMember666->relation_type = [
                                                                                    'en' => 'Granddaughter',
                                                                                    'gu' => 'પૌત્રી',
                                                                                ];
                                                                                $iMember555Children[] = $iMember666;
                                                                                unset($iMemberList[$iKey666]);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                $iMember555->children = $iMember555Children;

                                                                $iMember333Children[] = $iMember555;
                                                                unset($iMemberList[$iKey555]);
                                                            }
                                                        }
                                                        unset($iMemberList[$iKey444]);
                                                    } //  Grand Daughter Data
                                                    else {
                                                        if ($iMember444->gender == 'Female') {
                                                            $iMember444->relation_type = [
                                                                'en' => 'Niece',
                                                                'gu' => 'ભત્રીજી',
                                                            ];
                                                            $iMember333Children[] = $iMember444;
                                                            unset($iMemberList[$iKey444]);
                                                        }
                                                    }
                                                }
                                            }
                                            $iMember333->children = $iMember333Children;

                                            $iMember111Children[] = $iMember333;
                                            unset($iMemberList[$iKey333]);
                                        }
                                    }
                                    unset($iMemberList[$iKey222]);
                                } //  Daughter Data
                                else {
                                    if ($iMember222->gender == 'Female') {
                                        $iMember222->relation_type = [
                                            'en' => 'Sister',
                                            'gu' => 'બહેન',
                                        ];
                                        $iMember111Children[] = $iMember222;
                                        unset($iMemberList[$iKey222]);
                                    }
                                }
                            }
                        }

                        $iMember111->children = $iMember111Children;

                        $iFamily[] = $iMember111;
                        unset($iMemberList[$iKey111]);
                    }
                }
            }

            //  Set Grand Father
            if ($head_of_family_grand_father_id !== null) {
                foreach ($iMemberList as $iKey1111 => $iMember1111) {
                    if ($iMember1111->id == $head_of_family_grand_father_id && $iMember1111->gender == 'Male') {
                        $iMember1111->relation_type = [
                            'en' => 'Grand Father',
                            'gu' => 'દાદા',
                        ];
                        $iFamily[] = $iMember1111;
                        unset($iMemberList[$iKey1111]);
                    }
                }
            }

            //  Set Grand Mother
            if ($head_of_family_grand_mother_id !== null) {
                foreach ($iMemberList as $iKey11111 => $iMember11111) {
                    if ($iMember11111->id == $head_of_family_grand_mother_id && $iMember11111->gender == 'Female') {
                        $iMember11111->relation_type = [
                            'en' => 'Grand Mother',
                            'gu' => 'દાદીમા',
                        ];
                        $iMember11111Children = [];

                        //  Set Son And Daughter
                        foreach ($iMemberList as $iKey22222 => $iMember22222) {
                            if ($iMember22222->father_id == $head_of_family_grand_father_id && $iMember22222->mother_id == $head_of_family_grand_mother_id && $iMember22222->id !== $head_of_family_father_id) {
                                //  Son Data
                                if ($iMember22222->gender == 'Male') {
                                    $iMember22222->relation_type = [
                                        'en' => 'Uncle',
                                        'gu' => 'કાકા',
                                    ];
                                    $head_of_family_Son_id = $iMember22222->id;
                                    $head_of_family_Daughter_in_law_id = '';
                                    $iMember11111Children[] = $iMember22222;

                                    //  Set Daughter in Law
                                    foreach ($iMemberList as $iKey33333 => $iMember33333) {
                                        if ($iMember33333->relation_id == $head_of_family_Son_id && $iMember33333->gender == 'Female') {
                                            $head_of_family_Daughter_in_law_id = $iMember33333->id;
                                            $iMember33333->relation_type = [
                                                'en' => 'Aunt',
                                                'gu' => 'કાકી',
                                            ];
                                            $iMember33333Children = [];

                                            //  Set Grand Son And Grand Daughter
                                            foreach ($iMemberList as $iKey44444 => $iMember44444) {
                                                if ($iMember44444->father_id == $head_of_family_Son_id && $iMember44444->mother_id == $head_of_family_Daughter_in_law_id) {
                                                    //  Grand Son Data
                                                    if ($iMember44444->gender == 'Male') {
                                                        $iMember44444->relation_type = [
                                                            'en' => 'Cousin Brother',
                                                            'gu' => 'પિતરાઈ ભાઈ',
                                                        ];
                                                        $head_of_family_Grand_Son_id = $iMember44444->id;
                                                        $head_of_family_Grand_Daughter_in_law_id = '';
                                                        $iMember33333Children[] = $iMember44444;

                                                        //  Set Grand Daughter in Law
                                                        foreach ($iMemberList as $iKey55555 => $iMember55555) {
                                                            if ($iMember55555->relation_id == $head_of_family_Grand_Son_id && $iMember55555->gender == 'Female') {
                                                                $head_of_family_Grand_Daughter_in_law_id = $iMember55555->id;
                                                                $iMember55555->relation_type = [
                                                                    'en' => 'Cousin-Brother-in-law',
                                                                    'gu' => 'પિતરાઈ-ભાઈ-ભાભી',
                                                                ];
                                                                $iMember55555Children = [];
                                                                //  Set GreatGrand Son And GreatGrand Daughter
                                                                foreach ($iMemberList as $iKey66666 => $iMember66666) {
                                                                    if ($iMember66666->father_id == $head_of_family_Grand_Son_id && $iMember66666->mother_id == $head_of_family_Grand_Daughter_in_law_id) {
                                                                        //  GreatGrand Son Data
                                                                        if ($iMember66666->gender == 'Male') {
                                                                            $iMember66666->relation_type = [
                                                                                'en' => 'Cousin-Brother-nephew',
                                                                                'gu' => 'પિતરાઈ-ભાઈ-ભત્રીજા',
                                                                            ];
                                                                            $head_of_family_Great_Grand_Son_id = $iMember66666->id;
                                                                            $head_of_family_Great_Grand_Daughter_in_law_id = '';
                                                                            $iMember55555Children[] = $iMember66666;

                                                                            //  Set GreatGrand Daughter in Law
                                                                            foreach ($iMemberList as $iKey77777 => $iMember77777) {
                                                                                if ($iMember77777->relation_id == $head_of_family_Great_Grand_Son_id && $iMember77777->gender == 'Female') {
                                                                                    $head_of_family_Great_Grand_Daughter_in_law_id = $iMember77777->id;
                                                                                    $iMember77777->relation_type = [
                                                                                        'en' => 'Cousin-Brother-nephew-in-law',
                                                                                        'gu' => 'પિતરાઈ-ભાઈ-ભત્રીજા-વહુ',
                                                                                    ];
                                                                                    $iMember55555Children[] = $iMember77777;
                                                                                    unset($iMemberList[$iKey77777]);
                                                                                }
                                                                            }
                                                                            unset($iMemberList[$iKey66666]);
                                                                        } //  GreatGrand Daughter Data
                                                                        else {
                                                                            if ($iMember66666->gender == 'Female') {
                                                                                $iMember66666->relation_type = [
                                                                                    'en' => 'Cousin-Brother-niece',
                                                                                    'gu' => 'પિતરાઈ-ભાઈ-ભત્રીજી',
                                                                                ];
                                                                                $iMember55555Children[] = $iMember66666;
                                                                                unset($iMemberList[$iKey66666]);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                $iMember55555->children = $iMember55555Children;

                                                                $iMember33333Children[] = $iMember55555;
                                                                unset($iMemberList[$iKey55555]);
                                                            }
                                                        }
                                                        unset($iMemberList[$iKey44444]);
                                                    } //  Grand Daughter Data
                                                    else {
                                                        if ($iMember44444->gender == 'Female') {
                                                            $iMember44444->relation_type = [
                                                                'en' => 'Cousin Sister',
                                                                'gu' => 'પિતરાઈ બહેન',
                                                            ];
                                                            $iMember33333Children[] = $iMember44444;
                                                            unset($iMemberList[$iKey44444]);
                                                        }
                                                    }
                                                }
                                            }
                                            $iMember33333->children = $iMember33333Children;

                                            $iMember11111Children[] = $iMember33333;
                                            unset($iMemberList[$iKey33333]);
                                        }
                                    }
                                    unset($iMemberList[$iKey22222]);
                                } //  Daughter Data
                                else {
                                    if ($iMember22222->gender == 'Female') {
                                        $iMember22222->relation_type = [
                                            'en' => 'Father Sister',
                                            'gu' => 'પિતા બહેન',
                                        ];
                                        $iMember11111Children[] = $iMember22222;
                                        unset($iMemberList[$iKey22222]);
                                    }
                                }
                            }
                        }

                        $iMember11111->children = $iMember11111Children;

                        $iFamily[] = $iMember11111;
                        unset($iMemberList[$iKey11111]);
                    }
                }
            }

            if (count($iMemberList) > 0) {
                foreach ($iMemberList as $key => $val) {
                    $iFamily[] = $val->toArray();
                }
            }

            $value->member_list = $iFamily;

            return $value;
        });
    }

    public function getMemberNew($member_id = null): Collection
    {
        return (new MemberList($member_id))->get();
    }

    /**
     * @throws Throwable
     */
    public function delMember($member_id): array
    {
        DB::beginTransaction();
        try {
            $iMember = Member::find($member_id);
            if ($iMember !== null) {
                Log::info('First Function start:- ' . $iMember->id);
                // Self Side Check
                if ($iMember->head_of_the_family_id == '') {
                    $iAllMember = Member::headOfFamily($iMember->id)->pluck('id')->toArray();

                    Log::info('all data:-[' . implode(', ', $iAllMember) . ']');

                    foreach ($iAllMember as $member) {
                        $this->delMember($member);
                    }
                }

                // Father Side Check
                if ($iMember->gender == 'Male') {
                    $iChildFatherData = Member::father($iMember->id)->nullHeadOfTheFamily()
                        ->pluck('id')->toArray();

                    Log::info('male data:- [' . implode(', ', $iChildFatherData) . ']');
                    if (!empty($iChildFatherData)) {
                        foreach ($iChildFatherData as $key => $iMemberMale) {
                            $this->delMember($iMemberMale);
                        }
                    }

                    if (!empty($iMember->relation_id)) {
                        $this->delMember($iMember->relation_id);
                    }
                }

                // Mother Side Check
                if ($iMember->gender == 'Female') {
                    $iChildMotherData = Member::mother($iMember->id)->nullHeadOfTheFamily()
                        ->pluck('id')
                        ->toArray();

                    Log::info('female data:- [' . implode(', ', $iChildMotherData) . ']');

                    foreach ($iChildMotherData as $iMemberFemale) {
                        $this->delMember($iMemberFemale);
                    }

                    if (!empty($iMember->relation_id)) {
                        Member::where('id', $iMember->relation_id)->update(['relation_id' => null]);
                    }
                }
                $this->otherTableMemberDel($iMember->id);
                Member::destroy($iMember->id);
                Log::info('First Function end:- ' . $iMember->id);
            }
            DB::commit();
            $iReturn['message'] = 'Member Successfully Deleted';

            return $iReturn;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function otherTableMemberDel($member_id): void
    {
        try {
            Log::info('second function Start :- ' . $member_id);
            $member = Member::find($member_id);
            MemberGallery::where('member_id', $member_id)->delete();
            Committee::where('member_id', $member_id)->delete();
            Donation::where('member_id', $member_id)->delete();
            GameResult::where('caption_id', $member_id)->update(['caption_id' => null]);
            GameResult::where('wise_caption_id', $member_id)->update(['wise_caption_id' => null]);
            GameResult::where('man_of_the_match_id', $member_id)->update(['man_of_the_match_id' => null]);
            $member->gameResults()->each(function ($gameResult) {
                $gameResult->delete();
            });

            Game::where('man_of_the_series_id', $member_id)->update(['man_of_the_series_id' => null]);

            Result::where('member_id', $member_id)->delete();

            RoleUser::where('model_id', $member_id)->delete();

            Team::where('member_id', $member_id)->delete();

            Job::whereJsonContains('job_description->member_id', $member_id)->delete();

            UserForgotPasswordToken::where('user_id', $member_id)->orWhere('forgot_user_id', $member_id)->delete();

            Log::info('second function End :- ' . $member_id);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addUserLogin($member): JsonResponse|array
    {
        DB::beginTransaction();
        try {
            $return = [];
            if (!empty($member->phone) && !empty($member->birth_date) && $member->status == 'Active') {
                $from = new DateTime($member->birth_date);
                if ($from->diff(new DateTime('today'))->y > 18) {
                    $return['password'] = rand(100000, 999999);
                    $member->update(['password' => Hash::make($return['password'])]);
                } else {
                    Member::where('phone', $member->phone)->delete();
                }
            }
            DB::commit();

            return [
                'success' => true,
                'message' => 'Member added as login used successfully.',
                'data' => $return,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage());
        }
    }
}
