<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MemberRequestStoreRequest;
use App\Http\Requests\MemberRequestUpdateRequest;
use App\Http\Resources\MemberRequestResource;
use App\Models\MemberRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

class MemberRequestController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $length = $request->length ?: 20;

        $query = MemberRequest::query()->with('member', 'member.nativePlace', 'completedBy');

        if (auth()->user()->hasRole('Member')) {
            $query->where('member_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where(function ($mq2) use ($search) {
                          $mq2->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                               ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.gu"))) LIKE ?', ['%' . mb_strtolower($search) . '%']);
                      });
                  });
            });
        }

        $query->orderByRaw("FIELD(status, 'Pending', 'In Progress', 'Completed', 'Rejected')")
              ->orderBy('created_at', 'asc');

        $requests = $query->paginate($length)->withQueryString();

        return MemberRequestResource::collection($requests);
    }

    public function store(MemberRequestStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $memberRequest = MemberRequest::create([
                'member_id'   => auth()->id(),
                'subject'     => $request->subject,
                'description' => $request->description,
                'status'      => 'Pending',
                'created_by'  => auth()->id(),
            ]);

            DB::commit();

            return $this->successResponse('Request submitted successfully', MemberRequestResource::make($memberRequest), 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $memberRequest = MemberRequest::with('member', 'member.nativePlace', 'completedBy')->find($id);

            if (!$memberRequest) {
                throw new Exception('Request not found');
            }

            if (auth()->user()->hasRole('Member') && $memberRequest->member_id !== auth()->id()) {
                throw new Exception('Unauthorized access');
            }

            return $this->successResponse('Request details', MemberRequestResource::make($memberRequest));
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(MemberRequestUpdateRequest $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $memberRequest = MemberRequest::find($id);

            if (!$memberRequest) {
                throw new Exception('Request not found');
            }

            $updateData = [
                'status'        => $request->status,
                'admin_remarks' => $request->admin_remarks,
            ];

            if (in_array($request->status, ['Completed', 'Rejected'])) {
                $updateData['completed_at'] = now();
                $updateData['completed_by'] = auth()->id();
            }

            $memberRequest->update($updateData);

            DB::commit();

            $memberRequest->load('member', 'member.nativePlace', 'completedBy');

            return $this->successResponse('Request updated successfully', MemberRequestResource::make($memberRequest), 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }
}
