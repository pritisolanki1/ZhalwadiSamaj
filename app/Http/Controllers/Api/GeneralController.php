<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SearchMemberRequest;
use App\Http\Resources\CommitteeResource;
use App\Http\Resources\MemberShortResource;
use App\Http\Resources\ZoneResource;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Donation;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\Game;
use App\Models\GameResult;
use App\Models\Member;
use App\Models\MemberGallery;
use App\Models\Report;
use App\Models\Result;
use App\Models\Team;
use App\Models\Zone;
use App\Traits\MemberTraits;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Intervention\Image\Facades\Image;
use Spatie\Activitylog\Models\Activity;

class GeneralController extends ApiController
{
    use MemberTraits;

    public function index(): Response|JsonResponse|Application|ResponseFactory
    {
        try {
            $iData = [];

            $iData['dashboard']['total_member'] = Member::count();
            $iData['dashboard']['total_family'] = Member::whereNull('head_of_the_family_id')->count();
            $iData['dashboard']['total_donor'] = Donation::where(['transition_status' => Donation::TRANSITION_STATUS_DONE])->groupBy('member_id')->count();
            $iData['dashboard']['total_report'] = Report::count();

            $iData['members'] = $this->getMember();
            // $iData['member_filter'] = Member::select(['profession'=>function($query){return $query;}])->get()->groupBy('profession.en');
            $members = Member::with(['nativePlace'])->where('total_donation', '>', 0)->orderBy(
                'total_donation',
                'DESC'
            )->get();
            $iData['donations'] = MemberShortResource::collection($members);

            $iData['committees'] = [CommitteeResource::collection(Committee::loadRelationships()->get())->groupBy('authority_types.en')];
            $iData['teams'] = Team::GetAll();
            $iData['games'] = Game::GetAll();
            $iData['galleries'] = Gallery::latest()->get();
            $iData['results'] = Result::GetAll();
            $iData['result_years'] = Result::GetAllResultYear();
            $iData['zones'] = ZoneResource::collection(Zone::get());

            $iObject = GalleryImage::get()->merge(Donation::with([
                'member:id,head_of_the_family_id,name,avatar,native_place_id,total_donation',
                'member.nativePlace',
            ])->get())->merge(MemberGallery::with([
                'member:id,head_of_the_family_id,name,avatar,native_place_id,total_donation',
            ])->get())->sortBy('created_at')->reverse();

            $iData['fides']['total'] = count($iObject);
            $iData['fides']['last_page'] = ceil(count($iObject) / 10);
            $iData['fides']['data'] = array_values($iObject->forPage(1, 10)->toArray());

            return response($iData, 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getImage($width, $height, $path)
    {
        try {
            ini_set('memory_limit', '-1');
            $file = public_path($path);

            // Check if file exists before processing
            if (!file_exists($file) || !is_file($file)) {
                // Return a default placeholder image or 404 response
                return response()->json([
                    'error' => 'Image not found',
                    'message' => 'The requested image file does not exist'
                ], 404);
            }

            $img = Image::cache(function ($image) use ($file, $height, $width) {
                $image->make($file);
                if ($width != 0 && $height != 0) {
                    $image->resize($width, $height);
                }
            }, 10, true);

            return $img->response('jpg');
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('Image processing error: ' . $e->getMessage(), [
                'path' => $path,
                'width' => $width,
                'height' => $height
            ]);
            
            return response()->json([
                'error' => 'Image processing failed',
                'message' => 'Unable to process the requested image'
            ], 500);
        }
    }

    public function getalldatamember($memberid): Response|JsonResponse|Application|ResponseFactory
    {
        try {
            $iData['member'] = $this->getMember($memberid);

            return response($iData, 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getActivityLog(Request $request): Response|JsonResponse|Application|ResponseFactory
    {
        try {
            $length = $request->length > 0 ? $request->length : 10;
            // DB::enableQueryLog();
            $iData = ActivityLog::with([
                'user:id,name,email',
                'subjectUser:id,name,email',

                'committee:id,name,authority_types,designation,created_at',

                'galleryImage:id,gallery_id',
                'galleryImage.gallery:id,name',

                'result:id,member_id,class,year,type',
                'result.member:id,head_of_the_family_id,name',

                'member:id,name,native_place_id,kuldevi_id,zone_id,head_of_the_family_id,avatar',
                'member.zone:id,name',
                'member.kuldevi:id,name',
                'member.nativePlace:id,native',

                'gameResult:id,game_id,rank,team_name',
                'gameResult.game:id,game_name,game_name,game_type,year',

                'team:id,member_id,team_type',
                'team.member:id,head_of_the_family_id,name',

                'job:id,title',

                'donation:id,member_id',
                'donation.member:id,head_of_the_family_id,name,pancard',

                'causer',

                // 'announcement',
                // 'business',
                // 'donation',
                // 'gallery',
                // 'game',
                // 'kuldevi',
                // 'memberGallery',
                // 'nativePlaces',
                // 'report',
                // 'zone',
            ])
                // ->where('log_name', '!=', 'User')
                ->whereDate('created_at', '>', now()->subYear())->orderBy('id', 'DESC')->paginate($length);

            // dd(DB::getQueryLog());
            return response($iData, 200);
        } catch (Exception $e) {
            // throw $e;
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getActivityLogNew(Request $request): Response|JsonResponse|Application|ResponseFactory
    {
        try {
            $length = $request->length > 0 ? $request->length : 10;
            //            DB::enableQueryLog();
            $iData = Activity::with([
                'subject' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Donation::class     => ['member:id,name,avatar,native_place_id,pancard'],
                        GalleryImage::class => ['gallery:id,name'],
                        Result::class       => ['member:id,head_of_the_family_id,name'],
                        Member::class       => [
                            'Zone:id,name',
                            'Kuldevi:id,name',
                            'nativePlace:id,native',
                        ],
                        GameResult::class   => ['game:id,game_name,game_name,game_type,year'],
                        Team::class         => ['member:id,head_of_the_family_id,name'],
                        // Committee::class => [],
                        // Job::class => [],
                    ]);
                },
                'causer',
            ])->whereDate('created_at', '>', now()->subYear())->orderBy('id', 'DESC')->paginate($length);

            // dd(DB::getQueryLog());
            return response($iData, 200);
        } catch (Exception $e) {
            // throw $e;
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getFeedLog(Request $request): Response|JsonResponse|Application|ResponseFactory
    {
        try {
            $length = $request->length > 0 ? $request->length : 10;
            $page = $request->page > 0 ? $request->page : 1;
            $donation = Donation::with([
                'member:id,head_of_the_family_id,name,avatar,native_place_id,pancard,total_donation',
                'member.nativePlace',
            ])->get();
            $memberGallery = MemberGallery::with([
                'member:id,head_of_the_family_id,name,avatar,native_place_id,total_donation',
            ])->get();
            // DB::enableQueryLog();
            $iObject = GalleryImage::get()
                ->merge($donation)
                ->merge($memberGallery)
                ->sortBy('created_at')
                ->reverse();

            $iData['total'] = count($iObject);
            $iData['last_page'] = ceil(count($iObject) / $length);
            $iData['data'] = array_values($iObject->forPage($page, $length)->toArray());

            // dd(DB::getQueryLog());
            return response($iData, 200);
        } catch (Exception $e) {
            // throw $e;
            return $this->errorResponse($e->getMessage());
        }
    }

    public function searchMember(SearchMemberRequest $request): AnonymousResourceCollection
    {
        //        DB::enableQueryLog();
        $length = $request->length ?: 20;
        $members = Member::with([
            'headOfTheFamily',
            'nativePlace',
        ]);

        if ($request->filter_by_blood_group) {
            $members->where('blood_group', $request->filter_by_blood_group);
        }

        if ($request->filter_by_status || $request->filter_by_status == '0') {
            $members->where('status', $request->filter_by_status);
        }

        if ($request->filter_by_expired_member) {
            $members->whereNotNull('expire_date');
        }

        if ($request->filter_by_zone) {
            $members->whereHas('zone', function ($q) use ($request) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->filter_by_zone) . '%']);
            });
        }

        if ($request->filter_by_login_user) {
            $members->whereNotNull('device_token')->where('device_token', '!=', '');
        }

        if ($request->search_key && $searchValue = strtolower($request->search_value)) {
            switch ($request->search_key) {
                case 'name':
                    $members->where(function ($q) use ($searchValue) {
                        //                        $q->whereJsonContains('name->en', $searchValue)
                        //                            ->orWhereJsonContains('name->gu', $searchValue);
                        $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchValue . '%']);
                    });
                    break;
                case 'unique_number':
                    $members->where('unique_number', $searchValue);
                    break;
                case 'native':
                    $members->whereHas('nativePlace', function ($q) use ($searchValue) {
                        $q->whereRaw('LOWER(native) LIKE ?', ['%' . $searchValue . '%']);
                    });
                    break;
                case 'profession':
                    $members->where(function ($q) use ($searchValue) {
                        //                        $q->whereJsonContains('profession->en', $searchValue)
                        //                            ->orWhereJsonContains('profession->gu', $searchValue);
                        $q->whereRaw('LOWER(profession) LIKE ?', ['%' . $searchValue . '%']);
                    });
                    break;
            }
        }

        if ($request->filter_by_expired_member || $request->filter_by_status || $request->filter_by_status == '0') {
            $members = $members->get();
        } else {
            $members = $members->paginate($length)->withQueryString();
        }

        return MemberShortResource::collection($members);
    }

    public function comingMemberBirthday(Request $request): AnonymousResourceCollection
    {
        $length = $request->length ?: 20;
        //        DB::enableQueryLog();
        $members = Member::with([
            'headOfTheFamily',
            'nativePlace',
        ])->whereNotNull('birth_date')->whereNull('expire_date')->where(function ($query) {
            $query->whereMonth('birth_date', '>', now()->month)->orWhere(function ($query) {
                $query->whereMonth('birth_date', '=', now()->month)->whereDay('birth_date', '>=', now()->day);
            });
        })->orderByRaw("DATE_FORMAT(birth_date,'%m%d')")->orderByRaw("DATE_FORMAT(birth_date,'%y') desc")->orderBy('name_en');

        return MemberShortResource::collection($members->paginate($length)->withQueryString());
    }
}
