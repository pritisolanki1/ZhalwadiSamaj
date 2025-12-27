<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ZoneStoreRequest;
use App\Http\Resources\ZoneResource;
use App\Models\Zone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Zones",
 *     description="API endpoints for zone management"
 * )
 */
class ZoneController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/Zone/get_all",
     *     tags={"Zones"},
     *     summary="Get All Zones",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $zones = Zone::get();

        return ZoneResource::collection($zones);
    }

    public function store(ZoneStoreRequest $request): Response|JsonResponse
    {
        try {
            $request->validated();
            $zone = Zone::create($request->all());

            return $this->successResponse('Zone Created', ZoneResource::make($zone), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(ZoneStoreRequest $request, $id): Response|JsonResponse
    {
        try {
            if (!Zone::find($id)) {
                throw new Exception('zone not found');
            }
            $request->validated();
            $updatedFiled = $request->all();
            $zone = Zone::find($id)->fill($updatedFiled)->save();

            return $this->successResponse('zone Updated', ZoneResource::make($zone), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): Response|JsonResponse
    {
        try {
            $data = Zone::find($id)->delete();
            if (!$data) {
                return $this->errorResponse('zone not found/it is already been deleted', $data, 400);
            } else {
                return $this->successResponse('zone deleted', null, 201);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
