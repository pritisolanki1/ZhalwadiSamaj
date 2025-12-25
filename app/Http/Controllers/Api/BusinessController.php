<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BusinessStoreRequest;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BusinessController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $length = $request->length ?: 20;
        $businesses = Business::paginate($length)->withQueryString();

        return BusinessResource::collection($businesses);
    }

    public function store(BusinessStoreRequest $request): JsonResponse
    {
        try {
            $request->validated();
            $business = Business::create($request->all())->fresh();

            return $this->successResponse('Business Created', BusinessResource::make($business), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(BusinessStoreRequest $request, $id): JsonResponse
    {
        try {
            $business = Business::find($id);
            if (!$business->exists()) {
                throw new Exception('Business not found');
            }

            $request->validated();
            $updatedFiled = $request->all();
            $business->fill($updatedFiled)->save();

            return $this->successResponse('Business Updated', BusinessResource::make($business), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function upload_image(Request $request, $id): JsonResponse
    {
        try {
            $iObject = Business::find($id);
            if (!$iObject->exists()) {
                throw new Exception('Business not found');
            }
            $request->validate([
                'logo.*'    => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
                'slider.*'  => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
                'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            ]);

            $insertFiled['logo'] = [];
            $insertFiled['slider'] = [];
            $insertFiled['gallery'] = [];

            if ($request->hasFile('logo')) {
                $logos = $request->file('logo');
                foreach ($logos as $logo) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $logo->extension();
                    $logo->move('image/Business/logo/', $name);
                    $insertFiled['logo'][] = $name;
                }
            }
            if ($request->hasFile('slider')) {
                $sliders = $request->file('slider');
                foreach ($sliders as $slider) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $slider->extension();
                    $slider->move('image/Business/slider/', $name);
                    $insertFiled['slider'][] = $name;
                }
            }
            if ($request->hasFile('gallery')) {
                $gallerys = $request->file('gallery');
                foreach ($gallerys as $gallery) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $gallery->extension();
                    $gallery->move('image/Business/gallery/', $name);
                    $insertFiled['gallery'][] = $name;
                }
            }

            $business = Business::findOrFail($id);
            $business->logo = !empty($insertFiled['logo']) ? $insertFiled['logo'] : $business->logo;
            $business->slider = !empty($insertFiled['slider']) ? $insertFiled['slider'] : $business->slider;
            $business->gallery = !empty($insertFiled['gallery']) ? $insertFiled['gallery'] : $business->gallery;
            $business->save();

            return $this->successResponse('Records updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
