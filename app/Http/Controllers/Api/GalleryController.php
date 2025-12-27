<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\GalleryStoreRequest;
use App\Models\Gallery;
use App\Models\GalleryImage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @OA\Tag(
 *     name="Gallery",
 *     description="API endpoints for gallery management"
 * )
 */
class GalleryController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/gallery/get_all",
     *     tags={"Gallery"},
     *     summary="Get All Galleries",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $Gallery = Gallery::GetAll();

            return $this->successResponse('Gallery List', $Gallery);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(GalleryStoreRequest $request): JsonResponse
    {
        try {
            $request->validated();
            // dd($request->all());
            $Gallery = Gallery::create($request->all());

            return $this->successResponse('Gallery Created', $Gallery, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(GalleryStoreRequest $request, $id): JsonResponse
    {
        try {
            if (!Gallery::find($id)) {
                throw new Exception('Gallery not found');
            }

            $request->validated();
            $updatedFiled = $request->all();
            Gallery::find($id)->fill($updatedFiled)->save();
            $iRes = Gallery::GetAll($id);

            return $this->successResponse('Gallery Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            if (!Gallery::find($id)) {
                throw new Exception('Gallery not found');
            }
            $gallery = Gallery::with(['galleryImages'])->find($id);

            foreach ($gallery->galleryImages as $galleryImage) {
                $galleryImage->deleteMedia();
                $galleryImage->delete();
            }
            $gallery->delete();

            return $this->successResponse('Gallery deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function upload_image(Request $request, $id): JsonResponse
    {
        try {
            if (!Gallery::find($id)) {
                throw new Exception('Gallery not found');
            }
            $request->validate([
                'image.*'     => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
                // 'video.*' => 'mimetypes:video/mp4,video/3gpp,video/x-msvideo',
                'description' => 'filled',
            ]);

            $insertFiled['images'] = [];
            $insertFiled['videos'] = [];
            $insertFiled['description'] = $request->description;

            if ($request->hasFile('image')) {
                $images = $request->file('image');
                foreach ($images as $image) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $image->extension();
                    $image->move(public_path(Config::get('general.image_path.gallery_image.images')), $name);
                    $insertFiled['images'][] = $name;
                }
            }

            if ($request->hasFile('video')) {
                $videos = $request->file('video');
                foreach ($videos as $video) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $video->extension();
                    $video->move(public_path(Config::get('general.image_path.gallery_image.videos')), $name);
                    $insertFiled['videos'][] = $name;
                }
            }

            if (empty($insertFiled['images']) && empty($insertFiled['videos'])) {
                throw new Exception('gallery image not add successfully');
            }

            // DB::enableQueryLog();
            // $iGalleryImage = GalleryImage::where("gallery_id", $id)->whereDate('created_at', '=', date('Y-m-d'))->first();
            if (empty($iGalleryImage)) {
                $insertFiled['gallery_id'] = $id;
                $iGalleryImage = GalleryImage::create($insertFiled);
            }

            // else {
            //     foreach ($iGalleryImage->images as $image) {
            //         $array = explode('/', $image);
            //         array_push($insertFiled['images'], end($array));
            //     }
            //     foreach ($iGalleryImage->videos as $video) {
            //         $array = explode('/', $video);
            //         array_push($insertFiled['videos'], end($array));
            //     }
            //     $iGalleryImage->images = $insertFiled['images'];
            //     $iGalleryImage->videos = $insertFiled['videos'];
            //     $iGalleryImage->save();
            // }
            return $this->successResponse('Records updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
