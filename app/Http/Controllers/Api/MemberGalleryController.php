<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MemberGalleryStoreRequest;
use App\Http\Requests\MemberGalleryUpdateRequest;
use App\Http\Requests\MemberGalleryUploadRequest;
use App\Models\Member;
use App\Models\MemberGallery;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

/**
 * @OA\Tag(
 *     name="Member Gallery",
 *     description="API endpoints for member gallery management"
 * )
 */
class MemberGalleryController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/member_gallery/get_all",
     *     tags={"Member Gallery"},
     *     summary="Get All Member Galleries",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $MemberGallery = MemberGallery::orderBy('created_at', 'Desc')->get();

            return $this->successResponse('Gallery List', $MemberGallery);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(MemberGalleryStoreRequest $request): JsonResponse
    {
        try {
            $member_id = auth()->user()->hasRole('Member') ? auth()->user()->id : $request->member_id;
            if (!$member_gallery = MemberGallery::where('member_id', $member_id)->first()) {
                $member_gallery = new MemberGallery();
                $member_gallery->member_id = $member_id;
                $member_gallery->save();
            }

            return $this->successResponse('Member gallery add successfully', $member_gallery);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(MemberGalleryUpdateRequest $request, $id): JsonResponse
    {
        try {
            if (!MemberGallery::find($id)) {
                throw new Exception('Member gallery not found');
            }

            $request->validated();
            $updatedFiled = $request->all();
            $updatedFiled['images'] = makeLastImageValueSet($request->images);
            $updatedFiled['videos'] = makeLastImageValueSet($request->videos);
            MemberGallery::find($id)->fill($updatedFiled)->save();
            $iRes = MemberGallery::GetAll($id);

            return $this->successResponse('Gallery Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        if (!MemberGallery::find($id)) {
            return $this->errorResponse('Gallery Id not found', null, 404);
        }
        $Data = MemberGallery::find($id)->delete();

        return $this->successResponse('membergallery deleted successfully', $Data, 201);
    }

    public function upload_image(MemberGalleryUploadRequest $request, $Member_id): JsonResponse
    {
        try {
            if (!Member::find($Member_id)) {
                throw new Exception('Member not found');
            }

            $insertFiled['member_id'] = $Member_id;
            $insertFiled['images'] = [];
            $insertFiled['videos'] = [];
            if ($request->hasFile('image')) {
                $images = $request->file('image');
                foreach ($images as $image) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $image->extension();
                    $image->move(public_path(Config::get('general.image_path.member_gallery.images')), $name);
                    $insertFiled['images'][] = $name;
                }
            }
            if ($request->hasFile('video')) {
                $videos = $request->file('video');
                foreach ($videos as $video) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $video->extension();
                    $video->move(public_path(Config::get('general.image_path.member_gallery.videos')), $name);
                    $insertFiled['videos'][] = $name;
                }
            }
            if (empty($insertFiled['images']) && empty($insertFiled['videos'])) {
                throw new Exception('Member gallery not add successfully');
            }

            MemberGallery::create($insertFiled);

            return $this->successResponse('Member gallery add successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
