<?php

namespace App\Http\Controllers\Api;

use App\Filters\MemberFilters;
use App\Http\Requests\MemberStoreRequest;
use App\Http\Resources\MemberShortResource;
use App\Models\Member;
use App\Traits\MemberTraits;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Throwable;

class MemberController extends ApiController
{
    use MemberTraits;

    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->has('page') && $request->has('length')) {
                $length = (int)$request->input('length', 50);

                $members = Member::loadRelation()
                    ->whereNull('head_of_the_family_id')
                    ->orderByRaw('CAST(unique_number AS UNSIGNED) ASC');

                if ($request->filter_by_zone) {
                    $members->whereHas('zone', function ($q) use ($request) {
                        $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->filter_by_zone) . '%']);
                    });
                }

                $members = $members->paginate($length);

                $members->getCollection()->transform(function ($value) {
                    if ($value->head_of_the_family_id == null) {
                        $value->relation_type = [
                            'en' => 'Self',
                            'gu' => 'પોતે',
                        ];
                    }
                    return $value;
                });

                return response()->json([
                    'status' => 'Success',
                    'message' => 'Members List',
                    'data' => $members->items(),
                    'meta' => [
                        'last_page' => $members->lastPage(),
                        'current_page' => $members->currentPage(),
                        'total' => $members->total(),
                        'per_page' => (int)$members->perPage(),
                    ],
                ]);
            }

            $member = $this->getMember();

            return $this->successResponse('Members List', $member);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function indexNew(): JsonResponse
    {
        try {
            $member = $this->getMemberNew();

            return $this->successResponse('Members List', $member);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @throws Throwable
     */
    public function store(MemberStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validated();
            $iInsertFiled = $this->applyFamilyZoneOnStore($request->all());
            $iInsertFiled['name_en'] = $iInsertFiled['name']['en'];
            $iInsertFiled['status'] = 1;
            $member = Member::create($iInsertFiled)->assignRole('Member');
            $data['member_id'] = $member->id;
            $data['head_of_the_family_id'] = $member->head_of_the_family_id;
            $data['name'] = $member->name;
            $data['phone'] = $member->phone;
            $data['member_family_data'] = $this->getMember($member->id)[0];
            $loginData = $this->addUserLogin($member)['data'];
            if (isset($loginData['password'])) {
                $data['login_password'] = $loginData['password'];
            }
            DB::commit();

            return $this->successResponse('Member Created', $data, 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage());
        }
    }

    public function addLoginUser(): JsonResponse
    {
        try {
            $members = Member::where('birth_date', date('Y-m-d', strtotime('-18 year', time())))->where(
                'expire_date',
                null
            )->where('status', 1)->get();
            if ($members) {
                foreach ($members as $key => $member) {
                    $this->addUserLogin($member);
                }
            }

            return $this->successResponse('Successfully added member as user.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(Member $id): JsonResponse
    {
        try {
            $iData = $this->getMember($id->id)[0];

            return $this->successResponse('Successfully get member.', $iData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function showNew(Member $id): JsonResponse
    {
        try {
            $iData = $this->getMemberNew($id->id)[0];

            return $this->successResponse('Successfully get member.', $iData);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @throws Throwable
     */
    public function update(MemberStoreRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $iMember = Member::find($id);
            if (!$iMember) {
                throw new Exception('Member not found');
            }
            $request->validated();
            //dd($request->all("avatar"));
            $updatedFiled = $this->protectRelationshipIds($iMember, $request->all());
            $updatedFiled['name_en'] = $updatedFiled['name']['en'];
            $updatedFiled['birth_date'] = $updatedFiled['birth_date'] == null ? null : date_format(
                date_create($updatedFiled['birth_date']),
                'Y-m-d'
            );
            $updatedFiled['expire_date'] = $updatedFiled['expire_date'] == null ? null : date_format(
                date_create($updatedFiled['expire_date']),
                'Y-m-d'
            );
            // dd($updatedFiled["avatar"]);

            if ($updatedFiled['avatar'] != '') {
                unset($updatedFiled['avatar']);
            }

            // Family Zone rule: a family has exactly one Zone - the current head's.
            // A non-head member always persists with the CURRENT head's zone, so a
            // stale client snapshot can never pull a member out of the family Zone.
            $originalZoneId = $iMember->zone_id;
            $effectiveHeadId = array_key_exists('head_of_the_family_id', $updatedFiled)
                ? trim((string)$updatedFiled['head_of_the_family_id'])
                : trim((string)$iMember->head_of_the_family_id);
            $isHeadMember = $effectiveHeadId === '';
            if (!$isHeadMember) {
                $familyHead = Member::find($effectiveHeadId);
                if ($familyHead && !empty($familyHead->zone_id)) {
                    $updatedFiled['zone_id'] = $familyHead->zone_id;
                } else {
                    // Head has no zone yet: the member cannot lead the family Zone.
                    unset($updatedFiled['zone_id']);
                }
            }

            $iMember->fill($updatedFiled)->save();

            // The head defines the family Zone: propagate an intentional Zone change
            // to every current member of the family.
            if ($isHeadMember
                && array_key_exists('zone_id', $updatedFiled)
                && !empty($updatedFiled['zone_id'])
                && $updatedFiled['zone_id'] !== $originalZoneId) {
                Member::where('head_of_the_family_id', $iMember->id)
                    ->update(['zone_id' => $updatedFiled['zone_id']]);
            }

            $iRes = Member::find($id);

            DB::commit();

            return $this->successResponse('Member Updated', $iRes, 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Family Zone rule on create: a new member joining an existing family
     * (non-empty head_of_the_family_id) always lands in the current head's
     * Zone, regardless of what the client submitted. Members created as
     * their own head keep the submitted (validated) Zone.
     */
    private function applyFamilyZoneOnStore(array $fields): array
    {
        $headId = trim((string)($fields['head_of_the_family_id'] ?? ''));
        if ($headId === '') {
            return $fields;
        }

        $familyHead = Member::find($headId);
        if ($familyHead && !empty($familyHead->zone_id)) {
            $fields['zone_id'] = $familyHead->zone_id;
        }

        return $fields;
    }

    /**
     * Legacy Android builds submit untouched relationship fields as empty
     * values, and fill() would happily wipe the stored links with them.
     * An empty value only clears an existing relationship when the rest of
     * the payload expresses that intent, mirroring the app's own UI:
     *  - relation_id: the husband picker is visible for Married/Widow females,
     *    so an empty spouse id together with such a status means the picker
     *    simply failed to load -> keep the stored link. Any other status
     *    hides the picker, making an empty id a deliberate removal.
     *  - father_id/mother_id: the matching denormalised name travels along;
     *    if that name is unchanged the field was never loaded -> keep it.
     *    A changed or cleared name is a deliberate detach.
     *  - head_of_the_family_id: ownership changes only through the dedicated
     *    transfer endpoint, so an empty value never clears it.
     */
    private function protectRelationshipIds(Member $member, array $fields): array
    {
        foreach (['relation_id', 'father_id', 'mother_id', 'head_of_the_family_id'] as $field) {
            if (!array_key_exists($field, $fields)) {
                continue;
            }

            $value = $fields[$field];
            $isEmpty = $value === null || (is_string($value) && trim($value) === '');
            if (!$isEmpty) {
                continue;
            }

            if ($field === 'relation_id'
                && isset($fields['relationShip_status'])
                && in_array($fields['relationShip_status'], ['Married', 'Widow'], true)) {
                unset($fields[$field]);
                continue;
            }

            if (in_array($field, ['father_id', 'mother_id'], true)) {
                $nameField = $field === 'father_id' ? 'father_name' : 'mother_name';
                if ($this->denormalizedNameUnchanged($member->{$nameField}, $fields[$nameField] ?? null)) {
                    unset($fields[$field]);
                    continue;
                }
            }

            if ($field === 'head_of_the_family_id') {
                unset($fields[$field]);
                continue;
            }

            // Deliberate removal: write a clean NULL instead of ''.
            $fields[$field] = null;
        }

        return $fields;
    }

    /**
     * True when the incoming denormalised name equals the stored one
     * (both sides normalised: missing keys, '' and literal "null" strings
     * sent by the app all count as absent).
     */
    private function denormalizedNameUnchanged($storedName, $incomingName): bool
    {
        $normalize = function ($name) {
            if (!is_array($name)) {
                return [null, null];
            }

            return [
                isset($name['en']) && is_string($name['en']) && trim($name['en']) !== '' && $name['en'] !== 'null'
                    ? trim($name['en']) : null,
                isset($name['gu']) && is_string($name['gu']) && trim($name['gu']) !== '' && $name['gu'] !== 'null'
                    ? trim($name['gu']) : null,
            ];
        };

        return $normalize($storedName) === $normalize($incomingName);
    }

    public function destroy($id): JsonResponse
    {
        try {
            if (!Member::find($id)) {
                throw new Exception('Member not found');
            }
            $iData = $this->delMember($id);

            return $this->successResponse($iData['message']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function upload_image(Request $request, $id): JsonResponse
    {
        try {
            //dd($request->all());
            $Member = Member::find($id);
            if (!$Member) {
                throw new Exception('Member not found');
            }
            $request->validate([
                'avatar'   => 'image|mimes:jpeg,png,jpg,gif,svg',
                'slider.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
            ]);

            $insertFiled['avatar'] = '';
            $insertFiled['slider'] = [];

            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $oldAvatar = $Member->getRawOriginal('avatar');
                $name = md5(RandomStringGenerator(16) . time()) . '.' . $avatar->extension();
                processAndStoreImage($avatar, public_path(Config::get('general.image_path.member.avatar')), $name);
                $this->deleteMemberAvatarIfExists($oldAvatar);

                $insertFiled['avatar'] = $name;
            }

            if ($request->hasFile('slider')) {
                $this->deleteMemberSliderIfExists($Member);
                $sliders = $request->file('slider');
                foreach ($sliders as $slider) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $slider->extension();
                    processAndStoreImage($slider, public_path(Config::get('general.image_path.member.slider')), $name);
                    $insertFiled['slider'][] = $name;
                }
            }
            // $Member->avatar = !empty($insertFiled['avatar']) ? $insertFiled['avatar'] : $Member->avatar;
            // $Member->slider = !empty($insertFiled['slider']) ? $insertFiled['slider'] : $Member->slider;

            if (!empty($insertFiled['avatar'])) {
                $Member->avatar = $insertFiled['avatar'];
            }
            $Member->slider = $insertFiled['slider'];
            $Member->save();

            return $this->successResponse('Records updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function deleteMemberAvatarIfExists(?string $avatar): void
    {
        if (empty($avatar)) {
            return;
        }

        $avatarPath = public_path(
            trim(Config::get('general.image_path.member.avatar'), '/') . '/' . ltrim($avatar, '/')
        );

        if (File::exists($avatarPath) && File::isFile($avatarPath)) {
            File::delete($avatarPath);
        }
    }

    private function deleteMemberSliderIfExists($member): void
    {
        $sliders = jsonDecode($member->getRawOriginal('slider'));
        if (!is_array($sliders)) {
            return;
        }

        foreach ($sliders as $slider) {
            if (empty($slider)) {
                continue;
            }
            $sliderPath = public_path(Config::get('general.image_path.member.slider') . $slider);
            if (File::exists($sliderPath) && File::isFile($sliderPath)) {
                File::delete($sliderPath);
            }
        }
    }

    public function transferHeadOfFamily(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_head_id' => 'required|exists:members,id',
                'new_head_id'     => 'required|exists:members,id|different:current_head_id',
            ]);

            $currentHead = Member::findOrFail($request->current_head_id);
            $newHead = Member::findOrFail($request->new_head_id);

            if ($currentHead->head_of_the_family_id !== null && $currentHead->head_of_the_family_id !== '') {
                throw new Exception('Current member is not the head of the family.');
            }

            if ($newHead->head_of_the_family_id !== $currentHead->id) {
                throw new Exception('New head must be a member of the same family.');
            }

            DB::beginTransaction();

            Member::where('head_of_the_family_id', $currentHead->id)
                ->update(['head_of_the_family_id' => $newHead->id]);

            $newHeadUniqueNumber = $newHead->unique_number;
            $newHead->head_of_the_family_id = null;
            $newHead->unique_number = $currentHead->unique_number;
            $newHead->save();

            $currentHead->head_of_the_family_id = $newHead->id;
            $currentHead->unique_number = $newHeadUniqueNumber;
            $currentHead->save();

            // Family Zone rule: the NEW/current head's Zone is authoritative for
            // the whole family. Synchronize everyone inside this same transaction
            // so no intermediate mixed-Zone state is ever committed. When the new
            // head has no Zone yet there is nothing authoritative to sync to, so
            // Zones are left untouched (never invented here).
            if (!empty($newHead->zone_id)) {
                Member::where('head_of_the_family_id', $newHead->id)
                    ->update(['zone_id' => $newHead->zone_id]);
            }

            DB::commit();

            return $this->successResponse('Head of family transferred successfully.', [
                'old_head' => $currentHead->id,
                'new_head' => $newHead->id,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function block_member(Request $request): JsonResponse
    {
        try {
            $request_validation = [
                'member_id' => 'required|exists:members,id',
                'reason'    => 'sometimes',
            ];

            $validator = Validator::make($request->all(), $request_validation);
            if ($validator->fails()) {
                throw new Exception($validator->getMessageBag()->first());
            }

            $member = Member::find($request->member_id);
            if (!$member) {
                throw new Exception('Member not found.');
            }
            if ($member->status != 'Active') {
                throw new Exception('Member are already blocked.');
            }
            $token = $member->token();
            $token != null ? $token->revoke() : '';

            $member->reason = $request->reason;
            $member->status = '0';
            $member->save();

            return $this->successResponse('Member blocked successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function memberList(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'head_of_the_family_id' => ['filled', 'uuid', 'exists:members,id'],
            'member_id'             => ['filled', 'uuid', 'exists:members,id'],
            'search_type'           => ['required', 'in:father,mother,husband'],
            'relation_id'           => ['sometimes', 'uuid'],
        ]);

        $filters = new MemberFilters($request);

        $members = Member::query()
            ->filter($filters)
            ->groupBy('members.id')->get();

        return MemberShortResource::collection($members);
    }
}
