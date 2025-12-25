<?php

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Report */
class ReportResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'reportable_id'     => $this->reportable_id,
            'reportable_type'   => $this->reportable_type,
            'value'             => $this->value,
            'report_user_notes' => $this->report_user_notes,
            'action_user_id'    => $this->action_user_id,
            'action_user_notes' => $this->action_user_notes,
            'image'             => $this->image,
            'status'            => $this->status,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            'member_id'      => $this->member_id,
            'report_user_id' => $this->report_user_id,

            'member'        => MemberShortResource::make($this->whenLoaded('member')),
            'reported_user' => MemberShortResource::make($this->whenLoaded('reported_user')),
        ];
    }
}
