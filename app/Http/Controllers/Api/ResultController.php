<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ResultStoreRequest;
use App\Models\Result;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Results",
 *     description="API endpoints for result management"
 * )
 */
class ResultController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/result/get_all",
     *     tags={"Results"},
     *     summary="Get All Results",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $Result['AllData'] = Result::GetAll();
            $Result['result_year'] = Result::GetAllResultYear();

            return $this->successResponse('Results List', $Result);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(ResultStoreRequest $request): JsonResponse
    {
        try {
            $request->validated();
            // dd($request->all());

            $Result = Result::create($request->all());

            return $this->successResponse('Result Created', $Result, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(ResultStoreRequest $request, $id): JsonResponse
    {
        try {
            if (!Result::find($id)) {
                throw new Exception('Result not found');
            }
            $request->validated();
            $updatedFiled = $request->all();
            Result::find($id)->fill($updatedFiled)->save();
            $iRes = Result::GetAll($id);

            return $this->successResponse('Result Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $data = Result::find($id)->delete();
            if (!$data) {
                return $this->errorResponse('Result not found/it is already been deleted', $data, 400);
            } else {
                return $this->successResponse('Result  deleted', null, 201);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
