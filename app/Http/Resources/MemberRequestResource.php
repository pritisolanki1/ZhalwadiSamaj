<?php

namespace App\Http\Resources;

use App\Models\MemberRequest;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MemberRequest */
class MemberRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'member_id'      => $this->member_id,
            'subject'        => $this->subject,
            'description'    => $this->description,
            'status'         => $this->status,
            'admin_remarks'  => $this->admin_remarks,
            'created_by'     => $this->created_by,
            'completed_by'   => $this->completed_by,
            'completed_at'   => $this->completed_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,

            'member'         => MemberShortResource::make($this->whenLoaded('member')),
            'completed_by_user' => $this->when($this->completed_by, function () {
                return [
                    'id'   => $this->completedBy?->id,
                    'name' => $this->completedBy?->name,
                ];
            }),
        ];
    }
}
