<?php

namespace App\Http\Resources;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Donation */
class DonationResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'donations_type'    => $this->donations_type,
            'amount'            => $this->amount,
            'date'              => $this->date,
            'transition_id'     => $this->transition_id,
            'transition'        => $this->transition,
            'transition_status' => $this->transition_status,
            'status'            => $this->status,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            'member_id' => $this->member_id,

            'member' => MemberShortResource::make($this->whenLoaded('member')),
        ];
    }
}
