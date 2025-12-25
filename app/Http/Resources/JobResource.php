<?php

namespace App\Http\Resources;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Job */
class JobResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'business_id'     => $this->business_id,
            'title'           => $this->title,
            'job_description' => $this->job_description,
            'avatar'          => $this->avatar,
            'city'            => $this->city,
            'status'          => $this->status,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,

            'member_id' => $this->member_id,

            'member' => MemberShortResource::make($this->whenLoaded('member')),
        ];
    }
}
