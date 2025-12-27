<?php

namespace App\Http\Controllers\Api;

use App\Models\Member;
use App\Models\User;
use App\Models\UserForgotPasswordToken;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client as OClient;
use Throwable;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints for user authentication and authorization"
 * )
 */
class AuthController extends ApiController
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Admin/User Login",
     *     description="Login endpoint for admin and sub-admin users",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="remember_me", type="boolean", example=false),
     *             @OA\Property(property="device_token", type="string", example="device_token_here"),
     *             @OA\Property(property="device_serial", type="string", example="device_serial_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login Successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     * @throws Throwable
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
                'remember_me' => 'boolean',
            ]);

            $credentials = request([
                'email',
                'password',
            ]);
            if (!Auth::attempt($credentials)) {
                throw new Exception('Email id or password is wrong');
            }

            $user = User::with(['roles'])->where([
                'email' => $credentials['email'],
            ])->first();

            $iUpdate = [];

            if ($request->device_token != null) {
                $iUpdate['device_token'] = $request->device_token;
            }
            if ($request->device_serial != null) {
                $iUpdate['device_serial'] = $request->device_serial;
            }

            if (!empty($iUpdate)) {
                $user->update($iUpdate);
            }

            $user->access_token = $user->createToken('admin')->accessToken;

            return $this->successResponse('Login Successfully', $user);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string',
            ]);

            User::find(auth()->user()->id)->fill(['name' => $request->name])->save();

            return $this->successResponse('User updated successfully', null, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $deletingUser = User::find($request->user_id);

            if ((auth()->user()->hasRole('Admin') && $deletingUser->hasRole('Admin')) || !$deletingUser->hasRole(['Admin', 'SubAdmin'])) {
                throw new Exception('Unauthorized user for this action.');
            }

            $deletingUser->delete();

            return $this->successResponse('User deleted successfully.', null, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAdminList(): JsonResponse
    {
        try {
            $iUser = collect();
            if (User::find(auth()->user()->id)->hasRole('SuperAdmin')) {
                $iUser = User::with(['roles'])->whereHas('roles', function ($q) {
                    $q->whereIn('name', [
                        'Admin',
                        'SubAdmin',
                    ]);
                })->get();
            } elseif (User::find(auth()->user()->id)->hasRole('Admin')) {
                $iUser = User::with(['roles'])->whereHas('roles', function ($q) {
                    $q->whereIn('name', [
                        'SubAdmin',
                    ]);
                })->get();
            }

            return $this->successResponse('Admin list get successfully.', $iUser);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateDetailsFromParent(Request $request): JsonResponse
    {
        try {
            $customMessages = [
                'phone.unique' => 'This phone already taken another user.',
                'email.unique' => 'This email already taken another user.',
            ];

            $request_validation = [
                'type' => 'required|string|in:user,member',
            ];

            $validator = Validator::make($request->all(), $request_validation);
            if ($validator->fails()) {
                throw new Exception($validator->getMessageBag()->first());
            }

            if ($request->type == 'member') {
                $request_validation = [
                    'user_id' => 'required|exists:members,id',
                    'name' => 'required',
                    'phone' => 'filled|nullable|regex:/^[1-9]{1}[0-9]{9}/|unique:members,phone,' . $request->user_id,
                    'email' => 'filled|nullable|unique:members,email,' . $request->user_id,
                    'new_password' => 'required',
                    'status' => 'required',
                    'type' => 'required|string|in:user,member',
                ];

                $validator = Validator::make($request->all(), $request_validation, $customMessages);
                if ($validator->fails()) {
                    throw new Exception($validator->getMessageBag()->first());
                }

                $user = Member::findOrFail($request->user_id);
                $user->tokens()->delete();
                $user->fill([
                    'password' => Hash::make($request->new_password),
                    'status' => $request->status,
                ])->save();
            } else {
                $request_validation = [
                    'user_id' => 'required|exists:users,id',
                    'name' => 'required',
                    'phone' => 'filled|nullable|regex:/^[1-9]{1}[0-9]{9}/|unique:users,phone,' . $request->user_id,
                    'email' => 'filled|nullable|unique:users,email,' . $request->user_id,
                    'new_password' => 'required',
                    'status' => 'required',
                    'type' => 'required|string|in:user,member',
                ];

                $validator = Validator::make($request->all(), $request_validation, $customMessages);
                if ($validator->fails()) {
                    throw new Exception($validator->getMessageBag()->first());
                }

                $user = User::findOrFail($request->user_id);

                if (auth()->user()->hasRole('Admin') && $user->hasRole([
                    'Admin',
                    'SuperAdmin',
                ])) {
                    throw new Exception('Unauthorize user for this action.');
                }
                if (auth()->user()->hasRole('SubAdmin') && $user->hasRole([
                    'SubAdmin',
                    'Admin',
                    'SuperAdmin',
                ])) {
                    throw new Exception('Unauthorize user for this action.');
                }
                $user->tokens()->delete();
                $user->fill([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->new_password),
                    'status' => $request->status,
                ])->save();
            }

            return $this->successResponse('User update successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/member_login",
     *     tags={"Authentication"},
     *     summary="Member Login",
     *     description="Login endpoint for member users using phone number",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="password", type="string", format="password", example="123456"),
     *             @OA\Property(property="device_token", type="string", example="device_token_here"),
     *             @OA\Property(property="device_serial", type="string", example="device_serial_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Member login successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function member_login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|regex:/^[1-9]{1}[0-9]{9}/',
                'password' => 'required|string',
            ]);

            $credentials = request([
                'phone',
                'password',
            ]);

            if (!Auth::guard('member')->attempt($credentials)) {
                return $this->errorResponse('Phone number or password is wrong.', null, 401);
            }
            //            DB::enableQueryLog();
            $member = Member::with([
                'roles',
                'nativePlace',
                'memberGalleries',
            ])->find(auth('member')->user()->id);
            if (!$member->hasRole('Member')) {
                throw new Exception('You are not allow this login.');
            }

            if ($request->device_token != null) {
                $member->device_token = $request->device_token;
            }
            if ($request->device_serial != null) {
                $member->device_serial = $request->device_serial;
            }

            DB::table('oauth_access_tokens')->where('user_id', $member->id)->update([
                'revoked' => true,
            ]);

            $member->save();
            $member->access_token = $member->createToken('user')->accessToken;

            return $this->successResponse('Member login successfully', $member);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // public function getTokenAndRefreshToken($iData = [])
    // {
    //     try {
    //         $oClient = OClient::where('password_client', 1)->first();
    //         $http = new Client();
    //         $form_params = [
    //             'grant_type' => 'password',
    //             'client_id' => $oClient->id,
    //             'client_secret' => $oClient->secret,
    //             'scope' => '*',
    //         ];
    //         $form_params = array_merge($form_params, $iData);
    //         try {
    //             $response = $http->post(url('oauth/token'), [
    //                 'form_params' => $form_params,
    //             ]);
    //         } catch (Exception $e) {
    //             throw new HttpException(500, $e->getMessage());
    //         }

    //         $iRes = json_decode($response->getBody(), true);
    //         $iRes['expires_in'] = Carbon::parse(Carbon::now()->addDays(env(
    //             'ACCESS_TOKEN_EXPIRED',
    //             1
    //         )))->toDateTimeString();

    //         return $iRes;
    //     } catch (Exception $e) {
    //         throw new HttpException(500, $e->getMessage());
    //     }
    // }

    /**
     * @OA\Post(
     *     path="/api/signup",
     *     tags={"Authentication"},
     *     summary="Create Admin/SubAdmin User",
     *     description="Create a new admin or sub-admin user (requires authentication and Admin role)",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone", "password", "type"},
     *             @OA\Property(property="name", type="object", 
     *                 @OA\Property(property="en", type="string", example="John Doe"),
     *                 @OA\Property(property="gu", type="string", example="જ્હોન ડો")
     *             ),
     *             @OA\Property(property="email", type="string", format="email", example="subadmin@example.com"),
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="type", type="string", enum={"Admin", "SubAdmin"}, example="SubAdmin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     * @throws Throwable
     */
    public function signup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|array|size:2',
            'name.en' => 'required',
            'name.gu' => 'required',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|confirmed',
            'type' => 'required|string|in:Admin,SubAdmin',
        ]);

        try {
            if (User::find(auth()->user()->id)->hasRole('Admin')) {
                if ($request->type !== 'SubAdmin') {
                    throw new Exception('Unauthorize user for this action.');
                }
            }
            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ])->assignRole($request->type);
            DB::commit();

            return $this->successResponse('User created successfully.', $user, 201);
        } catch (Throwable $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout User",
     *     description="Logout the currently authenticated user and revoke their token",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->token()->revoke();
            auth()->user()->fill(['device_token' => ''])->save();

            return $this->successResponse('Successfully logged out');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function refresh(Request $request)
    {
        try {
            $oClient = OClient::where('password_client', 1)->first();
            $http = new Client();
            $response = $http->post(url('oauth/token'), [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => str_replace('Bearer ', '', $request->header('Authorization', '')),
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'scope' => '',
                ],
            ]);
            $iRes = json_decode($response->getBody(), true);
            $iRes['expires_in'] = Carbon::parse(Carbon::now()->addDays(env(
                'ACCESS_TOKEN_EXPIRED',
                1
            )))->toDateTimeString();

            return $iRes;
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        } catch (GuzzleException $e) {
        }
    }

    public function getMemberList(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|existsWithOther:members,phone,status,1',
            ]);
            $iMember = Member::select([
                'id',
                'head_of_the_family_id',
            ])->where('phone', $request->phone)->first();

            $head_of_the_family_id = $iMember->head_of_the_family_id == null ? $iMember->id : $iMember->head_of_the_family_id;
            $iMemberList = [$head_of_the_family_id];
            $iMemberListData = Member::select('members.id')->where(
                'head_of_the_family_id',
                $head_of_the_family_id
            )->whereNotIn('members.id', [$iMember->id])->where(
                'status',
                '1'
            )->whereNotNull('phone')->get()->pluck('id')->toArray();
            $iMemberList = array_merge($iMemberList, $iMemberListData);

            $iFinalMemberList = Member::select([
                'id',
                'avatar',
                'name',
            ])->whereIn('id', $iMemberList)->whereNotIn('id', [$iMember->id])->get();

            return $this->successResponse('Member list get successfully.', $iFinalMemberList);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function generateUserForgotPasswordToken(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|existsWithOther:members,phone,status,1',
                'user_id' => 'required|existsWithOther:members,id,status,1',
            ]);
            $member = Member::select([
                'id',
                'head_of_the_family_id',
                'name_en',
            ])->where('phone', $request->phone)->first();
            UserForgotPasswordToken::where('forgot_user_id', $member->id)->delete();
            UserForgotPasswordToken::create([
                'user_id' => $request->user_id,
                'forgot_user_id' => $member->id,
                'token' => rand(10000000, 99999999),
            ]);

            sendNotice(
                'Forgot password request.',
                $member->name_en . ' was send forgot password request on your mobile.',
                ['type' => 'forgot_password'],
                $request->user_id ? explode(',', $request->user_id) : []
            );

            return $this->successResponse('Member token generate successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getForgotUserList(): JsonResponse
    {
        try {
            $iMemberList = UserForgotPasswordToken::with(['member:id,name,avatar'])->where(
                'user_id',
                auth()->user()->id
            )->where(
                'created_at',
                '>=',
                Carbon::now()->subMinutes(30)->toDateTimeString()
            )->get();

            return $this->successResponse('Forgot user list get successfully.', $iMemberList);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // Forgot password Flow
    public function setNewPasswordForUser(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|existsWithOther:members,phone,status,1',
                'token' => 'required|exists:user_forgot_password_tokens,token',
                'new_password' => 'required|digits:6',
            ], ['token.exists' => 'Please enter valid token.']);

            $member = Member::select('members.id')->join('user_forgot_password_tokens', function ($join) {
                $join->on('members.id', '=', 'user_forgot_password_tokens.forgot_user_id');
            })->where('members.phone', $request->phone)->where(
                'user_forgot_password_tokens.token',
                $request->token
            )->where(
                'user_forgot_password_tokens.created_at',
                '>=',
                Carbon::now()->subMinutes(30)->toDateTimeString()
            )->first();

            if (!$member) {
                throw new Exception('User Not Found');
            }

            $member->password = Hash::make($request->new_password);
            $member->save();

            UserForgotPasswordToken::where('forgot_user_id', $member->id)->delete();

            return $this->successResponse('User password update successfully.');
        } catch (Exception $e) {
            if (!empty($e->errors())) {
                foreach ($e->errors() as $key => $value) {
                    return $this->errorResponse($value[0]);
                }
            } else {
                return $this->errorResponse($e->getMessage());
            }
        }
    }

    // admin Reset Password
    public function setNewPasswordFromAdmin(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|existsWithOther:members,phone,status,1',
                'new_password' => 'required|digits:6',
            ]);
            $member = Member::where('phone', $request->phone)->first();

            if (!$member) {
                throw new Exception('User Not Found');
            }

            $member->tokens->each(function ($token) {
                $token->delete();
            });

            $member->fill([
                'password' => Hash::make($request->new_password),
            ])->save();

            UserForgotPasswordToken::where('forgot_user_id', $member->id)->delete();

            return $this->successResponse('User password update successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // Change Password
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required',
            ]);

            $user = auth()->user();

            if (!Hash::check($request->old_password, $user->password)) {
                return $this->errorResponse('Old password is wrong.', null, 401);
            }

            $user->fill([
                'password' => Hash::make($request->new_password),
            ])->save();

            return $this->successResponse('User password update successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // User Generate Password Check For Head Of The family
    public function generatePasswordCheck(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|existsWithOther:members,phone,status,1',
            ]);

            $iMember = Member::where('members.phone', $request->phone)->where(
                'members.head_of_the_family_id',
                null
            )->where('phone_verified_at', null)->first();

            if ($iMember == null) {
                throw new Exception('User Not Found');
            }

            return $this->successResponse('User get successfully.', $iMember);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // User Generate Password For Head Of The family
    public function generatePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => 'required|existsWithOther:members,phone,status,1',
                'new_password' => 'required|digits:6',
            ]);

            $iMember = Member::where('members.phone', $request->phone)->where(
                'members.head_of_the_family_id',
                null
            )->where('phone_verified_at', null)->first();

            if ($iMember == null) {
                throw new Exception('User Not Found');
            }

            $iMember->password = Hash::make($request->new_password);
            $iMember->phone_verified_at = date('Y-m-d h:i:s');
            $iMember->save();

            return $this->successResponse('User password update successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
