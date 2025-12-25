<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CommitteeStoreRequest;
use App\Http\Resources\CommitteeResource;
use App\Models\Committee;
use Illuminate\Http\JsonResponse;

class CommitteeController extends ApiController
{
    public function index(): JsonResponse
    {
        $committees = Committee::loadRelationships()->get();

        return $this->successResponse(
            'Committee list',
            [CommitteeResource::collection($committees)->groupBy('authority_types.en')]
        );
    }

    public function store(CommitteeStoreRequest $request): JsonResponse
    {
        $committee = Committee::create($request->validated());

        return $this->successResponse('Committee Created', $committee, 201);
    }

    public function update(CommitteeStoreRequest $request, Committee $committee): JsonResponse
    {
        $committee->fill($request->validated())->save();

        return $this->successResponse('Committee Updated', CommitteeResource::make($committee));
    }

    public function destroy(Committee $committee): JsonResponse
    {
        $committee->delete();

        return $this->successResponse('committee deleted');
    }
}
