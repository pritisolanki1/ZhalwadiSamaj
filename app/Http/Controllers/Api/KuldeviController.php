<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\KuldeviStoreRequest;
use App\Models\Kuldevi;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class KuldeviController extends ApiController
{
    public function index(): Response|JsonResponse
    {
        try {
            $kuldevi = Kuldevi::all();

            return $this->successResponse('Kuldevi List', $kuldevi);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(KuldeviStoreRequest $request): Response|JsonResponse
    {
        try {
            $request->validated();
            // dd($request->all());
            $kuldevi = Kuldevi::create($request->all());

            return $this->successResponse('Kuldevi Created', $kuldevi, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(KuldeviStoreRequest $request, $id): Response|JsonResponse
    {
        try {
            if (!Kuldevi::find($id)) {
                throw new Exception('kuldevi not found');
            }
            $request->validated();
            $updatedFiled = $request->all();
            Kuldevi::find($id)->fill($updatedFiled)->save();
            $iRes = Kuldevi::GetAll($id);

            return $this->successResponse('kuldevi Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): Response|JsonResponse
    {
        try {
            $data = Kuldevi::find($id)->delete();
            if (!$data) {
                return $this->errorResponse('kuldevi not found/it is already been deleted', $data, 400);
            } else {
                return $this->successResponse('kuldevi deleted', null, 201);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
