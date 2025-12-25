<?php

namespace App\Http\Resources;

use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Committee */
class CommitteeResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'authority_types' => $this->authority_types,
            'phone'           => $this->phone,
            'designation'     => $this->designation,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,

            'member_id' => $this->member_id,
            'zone_id'   => $this->zone_id,

            'member' => MemberShortResource::make($this->whenLoaded('member')),
            'zone'   => ZoneResource::make($this->whenLoaded('zone')),
        ];
    }
}
