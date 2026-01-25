<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\CommitteeController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\GalleryImageController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameResultController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\KuldeviController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MemberGalleryController;
use App\Http\Controllers\Api\NativePlacesController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\ZoneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Health check endpoint for Railway deployment
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toIso8601String(),
        'environment' => config('app.env'),
    ]);
});

// Route::group(['middleware' => ['throttle:3,3,route_admin']], function () {
Route::post('/login', [AuthController::class, 'login']);
// });
// Route::group(['middleware' => ['throttle:3,3,route_user']], function () {
Route::post('/member_login', [AuthController::class, 'member_login']);
// });
Route::post('/login/refresh', [AuthController::class, 'refresh']);

Route::get('/addLoginUser', [MemberController::class, 'addLoginUser']);
Route::get('/all', [GeneralController::class, 'index']);
Route::put('/getallmember/{id}', [GeneralController::class, 'getalldatamember']);
Route::get('member/search_member', [GeneralController::class, 'searchMember']);
Route::get('member/coming_member_birthday', [GeneralController::class, 'comingMemberBirthday']);

// Forgot Password Setup
Route::post('/getMemberList', [AuthController::class, 'getMemberList']);
Route::post('/generateUserForgotPasswordToken', [AuthController::class, 'generateUserForgotPasswordToken']);
Route::post('/setNewPasswordForUser', [AuthController::class, 'setNewPasswordForUser']);

// Head Of The Family Generate Password
Route::post('/generatePasswordCheck', [AuthController::class, 'generatePasswordCheck']);
Route::post('/generatePassword', [AuthController::class, 'generatePassword']);

// role Wise Permission Set
// https://spatie.be/docs/laravel-permission/v3/basic-usage/middleware
// Route::group(['middleware' => ['auth:api', 'role:Admin']], function () {
// });

Route::group(['middleware' => 'auth:user-api,member-api'], function () {
    Route::post('/getActivityLog', [GeneralController::class, 'getActivityLog']);
    Route::post('/getActivityLogNew', [GeneralController::class, 'getActivityLogNew']);
    Route::post('/getFeedLog', [GeneralController::class, 'getFeedLog']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::post('member-list', [MemberController::class, 'memberList']);
    Route::group(['middleware' => 'auth:user-api'], function () {
        Route::post('/member/block', [MemberController::class, 'block_member']);
        Route::group(['middleware' => ['role:SuperAdmin|Admin']], function () {
            Route::post('/signup', [AuthController::class, 'signup']);
            Route::post('/delete', [AuthController::class, 'delete']);
            Route::get('/getAdminList', [AuthController::class, 'getAdminList']);
            // Route::post('/setNewPasswordFromAdmin', [AuthController::class,'setNewPasswordFromAdmin']);
        });

        Route::group(['middleware' => ['role:SuperAdmin|Admin|SubAdmin']], function () {
            Route::post('/updateDetailsFromParent', [AuthController::class, 'updateDetailsFromParent']);
        });
    });

    Route::post('/update', [AuthController::class, 'update']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::group(['middleware' => ['role:Member']], function () {
        Route::get('/getForgotUserList', [AuthController::class, 'getForgotUserList']);
    });

    Route::post('/changePassword', [AuthController::class, 'changePassword']);

    //     //  Make Event Route
    // Route::apiResource('events', 'Api\EventController');
    //  GET|HEAD                               | api/events                                             | App\Http\Controllers\Api\EventController@index                            |
    //  POST                                   | api/events                                             | App\Http\Controllers\Api\EventController@store                            |
    //  DELETE                                 | api/events/{event}                                     | App\Http\Controllers\Api\EventController@destroy                          |
    //  PUT|PATCH                              | api/events/{event}                                     | App\Http\Controllers\Api\EventController@update                           |
    //  GET|HEAD                               | api/events/{event}                                     | App\Http\Controllers\Api\EventController@show                             |

    //  GET|HEAD                               | api/event/get_all                                      | App\Http\Controllers\Api\EventController@index                            |
    //  POST                                   | api/event/store                                        | App\Http\Controllers\Api\EventController@store                            |
    //  DELETE                                 | api/event/delete/{id}                                  | App\Http\Controllers\Api\EventController@destroy                          |
    //  PUT                                    | api/event/update/{id}                                  | App\Http\Controllers\Api\EventController@update                           |

    //  Make Game Route
    Route::group(['prefix' => 'game'], function () {
        Route::get('/get_all', [GameController::class, 'index']);
        Route::post('/store', [GameController::class, 'store']);
        Route::put('/update/{id}', [GameController::class, 'update']);
        Route::delete('delete/{id}', [GameController::class, 'destroy']);
    });

    //  Make Game Result Route
    Route::group(['prefix' => 'game_result'], function () {
        Route::get('/get_all', [GameResultController::class, 'index']);
        Route::post('/store', [GameResultController::class, 'store']);
        Route::put('/update/{id}', [GameResultController::class, 'update']);
        Route::delete('/delete/{id}', [GameResultController::class, 'destroy']);
        Route::post('/upload_image/{id}', [GameResultController::class, 'upload_image']);
    });

    //  Member Route Make
    Route::group(['prefix' => 'member'], function () {
        Route::get('/get_all', [MemberController::class, 'index']);
        Route::get('/get_all_new', [MemberController::class, 'indexNew']);
        Route::post('/store', [MemberController::class, 'store']);
        Route::get('/get/{id}', [MemberController::class, 'show']);
        Route::get('/get_new/{id}', [MemberController::class, 'showNew']);
        Route::put('/update/{id}', [MemberController::class, 'update']);
        Route::post('/upload_image/{id}', [MemberController::class, 'upload_image']);
        Route::delete('delete/{id}', [MemberController::class, 'destroy']);
    });

    //  Business Route Make
    Route::group(['prefix' => 'business'], function () {
        Route::get('/get_all', [BusinessController::class, 'index']);
        Route::post('/store', [BusinessController::class, 'store']);
        Route::put('/update/{id}', [BusinessController::class, 'update']);
        Route::post('/upload_image/{id}', [BusinessController::class, 'upload_image']);
        Route::delete('delete/{id}', [BusinessController::class, 'destroy']);
    });

    //  Committee Route Make
    Route::apiResource('committee', CommitteeController::class)->except(['show']);

    //    Route::group(['prefix' => 'committee'], function () {
    //        Route::get('/get_all', [CommitteeController::class,'index']);
    //        Route::post('/store', [CommitteeController::class,'store']);
    //        Route::put('/update/{id}', [CommitteeController::class,'update']);
    //        //Route::post('/upload_image/{id}' , [CommitteeController::class,'upload_image']);
    //        Route::delete('delete/{id}', [CommitteeController::class,'destroy']);
    //    });

    //  Donation Route Make
    Route::group(['prefix' => 'donation'], function () {
        Route::get('/get_all', [DonationController::class, 'index']);
        Route::get('/get_all_total', [DonationController::class, 'index_total']);
        Route::post('/store', [DonationController::class, 'store']);
        Route::put('/update/{id}', [DonationController::class, 'update']);
        Route::delete('delete/{id}', [DonationController::class, 'destroy']);
    });

    //  Announcement Route Make
    Route::group(['prefix' => 'announcement'], function () {
        Route::get('/get_all', [AnnouncementController::class, 'index']);
        Route::post('/store', [AnnouncementController::class, 'store']);
        Route::put('/update/{id}', [AnnouncementController::class, 'update']);
        Route::delete('delete/{id}', [AnnouncementController::class, 'destroy']);
    });

    //  Gallery Route Make
    Route::group(['prefix' => 'gallery'], function () {
        Route::get('/get_all', [GalleryController::class, 'index']);
        Route::post('/store', [GalleryController::class, 'store']);
        Route::put('/update/{id}', [GalleryController::class, 'update']);
        Route::post('/upload_image/{id}', [GalleryController::class, 'upload_image']);
        Route::delete('/delete/{id}', [GalleryController::class, 'destroy']);
    });

    //  GalleryImage Route Make
    Route::group(['prefix' => 'gallery_image'], function () {
        Route::put('/update/{id}', [GalleryImageController::class, 'update']);
        Route::delete('delete/{id}', [GalleryImageController::class, 'destroy']);
    });

    //  Result Route Make
    Route::group(['prefix' => 'result'], function () {
        Route::get('/get_all', [ResultController::class, 'index']);
        Route::post('/store', [ResultController::class, 'store']);
        Route::put('/update/{id}', [ResultController::class, 'update']);
        Route::delete('delete/{id}', [ResultController::class, 'destroy']);
    });

    //  NativePlace Route Make
    Route::group(['prefix' => 'nativePlaces'], function () {
        Route::get('/get_all', [NativePlacesController::class, 'index']);
        Route::post('/store', [NativePlacesController::class, 'store']);
        Route::put('/update/{id}', [NativePlacesController::class, 'update']);
        Route::delete('delete/{id}', [NativePlacesController::class, 'destroy']);
    });

    //  Kuldevi Route Make
    Route::group(['prefix' => 'Kuldevi'], function () {
        Route::get('/get_all', [KuldeviController::class, 'index']);
        Route::post('/store', [KuldeviController::class, 'store']);
        Route::put('/update/{id}', [KuldeviController::class, 'update']);
        Route::delete('delete/{id}', [KuldeviController::class, 'destroy']);
    });

    //  Zone Route Make
    Route::group(['prefix' => 'Zone'], function () {
        Route::get('/get_all', [ZoneController::class, 'index']);
        Route::post('/store', [ZoneController::class, 'store']);
        Route::put('/update/{id}', [ZoneController::class, 'update']);
        Route::delete('delete/{id}', [ZoneController::class, 'destroy']);
    });

    //  Job Route Make
    Route::group(['prefix' => 'job'], function () {
        Route::get('/get_all', [JobController::class, 'index']);
        Route::post('/store', [JobController::class, 'store']);
        Route::put('/update/{id}', [JobController::class, 'update']);
        Route::delete('delete/{id}', [JobController::class, 'destroy']);
        Route::post('/upload_image/{id}', [JobController::class, 'upload_image']);
    });

    //  Team Route Make
    Route::group(['prefix' => 'team'], function () {
        Route::get('/get_all', [TeamController::class, 'index']);
        Route::post('/store', [TeamController::class, 'store']);
        Route::put('/update/{id}', [TeamController::class, 'update']);
        Route::post('/upload_image/{id}', [TeamController::class, 'upload_image']);
        Route::delete('delete/{id}', [TeamController::class, 'destroy']);
    });

    //  Gallery Route Make
    Route::group(['prefix' => 'member_gallery'], function () {
        Route::get('/get_all', [MemberGalleryController::class, 'index']);
        Route::post('/store', [MemberGalleryController::class, 'store']);
        Route::put('/update/{id}', [MemberGalleryController::class, 'update']);
        Route::post('/upload_image/{id}', [MemberGalleryController::class, 'upload_image']);
        Route::delete('/delete/{id}', [MemberGalleryController::class, 'destroy']);
    });

    Route::apiResource('reports', ReportController::class);
});
