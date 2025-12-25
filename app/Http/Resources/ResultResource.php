<?php

namespace App\Http\Resources;

use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Result */
class ResultResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'year'       => $this->year,
            'class'      => $this->class,
            'class_type' => $this->class_type,
            'percentage' => $this->percentage,
            'percentile' => $this->percentile,
            'status'     => $this->status,
            'type'       => $this->type,
            'medium'     => $this->medium,
            'rank'       => $this->rank,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'member_id' => $this->member_id,

            'member' => MemberShortResource::make($this->whenLoaded('member')),
        ];
    }
}
