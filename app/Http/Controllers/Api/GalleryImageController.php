<?php

namespace App\Http\Controllers\Api;

use App\Models\GalleryImage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// use App\Http\Requests\GalleryImageStoreRequest;

/**
 * @OA\Tag(
 *     name="Gallery Images",
 *     description="API endpoints for gallery image management"
 * )
 */
class GalleryImageController extends ApiController
{
    /**
     * @OA\Put(
     *     path="/api/gallery_image/update/{id}",
     *     tags={"Gallery Images"},
     *     summary="Update Gallery Image",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function update(Request $request, $id): Response|JsonResponse
    {
        try {
            if (!GalleryImage::find($id)) {
                throw new Exception('Gallery not found');
            }
            $galleryImage = GalleryImage::find($id);
            $galleryImage->images = makeLastImageValueSet($request->images);
            $galleryImage->videos = makeLastImageValueSet($request->videos);
            $galleryImage->description = $request->description;
            checkDeferenceDeleteMedia($galleryImage->images, $galleryImage->getOriginal('images'));

            if ($galleryImage->images == [] && $galleryImage->videos == []) {
                $galleryImage->delete();
            } else {
                $galleryImage->save();
            }

            $iRes = GalleryImage::GetAll($id);

            return $this->successResponse('Gallery Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function destroy($gallery_id): Response|JsonResponse
    {
        if (!GalleryImage::find($gallery_id)->exists()) {
            throw new Exception('gallery not found');
        }
        $galleryImage = GalleryImage::find($gallery_id);
        $galleryImage->deleteMedia();
        $galleryImage->delete();

        return $this->successResponse('Gallery deleted', null, 201);
    }
}
