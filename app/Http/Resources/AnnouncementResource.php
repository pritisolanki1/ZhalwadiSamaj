<?php

namespace App\Http\Resources;

use App\Models\Announcement;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Announcement */
class AnnouncementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'image'       => $this->image,
            'deleted_at'  => $this->deleted_at,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'members' => MemberShortResource::collection($this->whenLoaded('members')),
        ];
    }
}
