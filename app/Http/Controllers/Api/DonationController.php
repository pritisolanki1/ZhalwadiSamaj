<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DonationStoreRequest;
use App\Http\Resources\DonationResource;
use App\Http\Resources\MemberShortResource;
use App\Models\Donation;
use App\Models\Member;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DonationController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $length = $request->length ?: 20;
        $donations = Donation::loadRelationships()->paginate($length)->withQueryString();

        return DonationResource::collection($donations);
    }

    public function index_total(): JsonResponse
    {
        try {
            $members = Member::with(['nativePlace'])->where('total_donation', '>', 0)->orderBy(
                'total_donation',
                'DESC'
            )->get();

            return $this->successResponse('Donations List', MemberShortResource::collection($members));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(DonationStoreRequest $request): JsonResponse
    {
        try {
            $request->validated();
            $donation = Donation::create($request->all())->fresh();
            if ($donation->status == Donation::STATUS_ACTIVE && $donation->transition_status == Donation::TRANSITION_STATUS_DONE && ($donation->donations_type == Donation::DONATION_TYPE_CASH || $donation->donations_type == Donation::DONATION_TYPE_ONLINE)) {
                $donation->member->syncDonation();
            }

            return $this->successResponse('Donation Created', DonationResource::make($donation), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(DonationStoreRequest $request, $id): JsonResponse
    {
        try {
            if (!Donation::exists($id)) {
                throw new Exception('Donation not found');
            }
            $donation = Donation::find($id);

            $donation->fill($request->validated())->save();
            $donation->fresh();

            if ($donation->status == Donation::STATUS_ACTIVE && $donation->transition_status == Donation::TRANSITION_STATUS_DONE && ($donation->donations_type == Donation::DONATION_TYPE_CASH || $donation->donations_type == Donation::DONATION_TYPE_ONLINE)) {
                $donation->member->syncDonation();
            }

            return $this->successResponse('Donation Updated', DonationResource::make($donation), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $donation = Donation::find($id);
            $member = $donation->member;
            $donation->delete();
            $member->syncDonation();
            if (!$donation) {
                return $this->errorResponse('Donation not found/it is already been deleted', $donation, 400);
            } else {
                return $this->successResponse('Donation deleted', $donation);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
