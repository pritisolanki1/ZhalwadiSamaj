<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\NativePlacesStoreRequest;
use App\Http\Resources\NativePlaceResource;
use App\Models\NativePlace;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NativePlacesController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        $nativePlaces = NativePlace::get();

        return NativePlaceResource::collection($nativePlaces);
    }

    public function store(NativePlacesStoreRequest $request): JsonResponse
    {
        try {
            $request->validated();
            $nativePlace = NativePlace::create($request->all());

            return $this->successResponse('NativePlace Created', NativePlaceResource::make($nativePlace), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(NativePlacesStoreRequest $request, $id): JsonResponse
    {
        try {
            $nativePlace = NativePlace::find($id);
            if (!$nativePlace) {
                throw new Exception('NativePlace not found');
            }
            $request->validated();
            $nativePlace->fill($request->all())->save();

            return $this->successResponse('NativePlace Updated', NativePlaceResource::make($nativePlace));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $nativePlace = NativePlace::find($id);
            if (!$nativePlace) {
                return $this->errorResponse('NativePlace not found/it is already been deleted', null, 400);
            }
            $nativePlace->delete();
            return $this->successResponse('NativePlace deleted', null, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /*public function upload_image( Request $request , $id )
    {
        try {
            if (!NativePlace::find($id) ) {
                throw new Exception("NativePlace not found");
            }
            $request->validate([
                'avatar'   => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048' ,
                'slider.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048' ,
            ]);

            $insertFiled['avatar'] = '';
            $insertFiled['slider'] = [];

            if ( $request->hasFile('avatar') ) {
                $avatar = $request->file('avatar');
                $name = md5(RandomStringGenerator(16) . time()) . '.' . $avatar->extension();
                $avatar->move('image/NativePlace/avatar/' , $name);
                $insertFiled['avatar'] = $name;
            }
            if ( $request->hasFile('slider') ) {
                $sliders = $request->file('slider');
                foreach ( $sliders as $slider ) {
                    $name = md5(RandomStringGenerator(16) . time()) . '.' . $slider->extension();
                    $slider->move('image/NativePlace/slider/' , $name);
                    $insertFiled['slider'][] = $name;
                }
            }
            $NativePlace = NativePlace::find($id);
            $NativePlace->avatar = !empty($insertFiled['avatar']) ? $insertFiled['avatar'] : $NativePlace->avatar;
            $NativePlace->slider = !empty($insertFiled['slider']) ? $insertFiled['slider'] : $NativePlace->slider;
            $NativePlace->save();

            return $this->successResponse('Records updated successfully.');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }


    }*/
}
