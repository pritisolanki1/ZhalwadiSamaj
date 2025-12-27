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
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * @OA\Tag(
 *     name="Members",
 *     description="API endpoints for member management"
 * )
 */
class MemberController extends ApiController
{
    use MemberTraits;

    /**
     * @OA\Get(
     *     path="/api/member/get_all",
     *     tags={"Members"},
     *     summary="Get All Members",
     *     description="Retrieve a list of all members",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Members list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Members List"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
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
     * @OA\Post(
     *     path="/api/member/store",
     *     tags={"Members"},
     *     summary="Create New Member",
     *     description="Create a new member record",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone"},
     *             @OA\Property(property="name", type="object", 
     *                 @OA\Property(property="en", type="string", example="John Doe"),
     *                 @OA\Property(property="gu", type="string", example="જ્હોન ડો")
     *             ),
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="email", type="string", format="email", example="member@example.com"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Member created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Member Created"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     * @throws Throwable
     */
    public function store(MemberStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validated();
            $iInsertFiled = $request->all();
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

    /**
     * @OA\Get(
     *     path="/api/member/get/{id}",
     *     tags={"Members"},
     *     summary="Get Member by ID",
     *     description="Retrieve a specific member by ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Member ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully get member."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
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
            if (!Member::find($id)) {
                throw new Exception('Member not found');
            }
            $request->validated();
            //dd($request->all("avatar"));
            $updatedFiled = $request->all();
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
            Member::find($id)->fill($updatedFiled)->save();

            $iRes = Member::find($id);
            $this->addUserLogin($iRes->toArray());

            DB::commit();

            return $this->successResponse('Member Updated', $iRes, 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage());
        }
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
        }
    }

    public function upload_image(Request $request, $id): JsonResponse
    {
        try {
            //dd($request->all());
            if (!Member::find($id)) {
                throw new Exception('Member not found');
            }
            $request->validate([
                'avatar'   => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
                'slider.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            ]);

            $insertFiled['avatar'] = '';
            $insertFiled['slider'] = [];

            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $name = md5(RandomStringGenerator(16) . time()) . '.' . $avatar->extension();
                $avatar->move(public_path(Config::get('general.image_path.member.avatar')), $name);
                $insertFiled['avatar'] = $name;
            }
            // if ( $request->hasFile('slider') ) {
            //     $sliders = $request->file('slider');
            //     foreach ( $sliders as $slider ) {
            //         $name = md5(RandomStringGenerator(16) . time()) . '.' . $slider->extension();
            //    // $slider->move(public_path(Config::get('general.image_path.member.slider')) , $name);

            //         $slider->move('image/Member/slider/' , $name);
            //         $insertFiled['slider'][] = $name;
            //     }
            // }

            if ($request->hasFile('slider')) {
                $sliders = $request->file('slider');
                foreach ($sliders as $slider) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $slider->extension();
                    $slider->move(public_path(Config::get('general.image_path.member.slider')), $name);
                    $insertFiled['slider'][] = $name;
                }
            }
            $Member = Member::find($id);

            foreach ($Member->slider as $slider1) {
                $array = explode('/', $slider1);
                $insertFiled['slider'][] = end($array);
            }
            // $Member->avatar = !empty($insertFiled['avatar']) ? $insertFiled['avatar'] : $Member->avatar;
            // $Member->slider = !empty($insertFiled['slider']) ? $insertFiled['slider'] : $Member->slider;

            $Member->avatar = $insertFiled['avatar'];
            $Member->slider = $insertFiled['slider'];
            $Member->save();

            return $this->successResponse('Records updated successfully.');
        } catch (Exception $e) {
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
            'relation_id'           => ['required_if:search_type,==,mother', 'uuid'],
        ]);

        $filters = new MemberFilters($request);

        $members = Member::query()
            ->filter($filters)
            ->groupBy('members.id')->get();

        return MemberShortResource::collection($members);
    }
}
