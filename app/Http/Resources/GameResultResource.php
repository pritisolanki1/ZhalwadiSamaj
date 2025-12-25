<?php

namespace App\Http\Resources;

use App\Models\GameResult;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GameResult */
class GameResultResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'team_name'     => $this->team_name,
            'image'         => $this->image,
            'rank'          => $this->rank,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'members_count' => $this->members_count,
            'game_id'       => $this->game_id,

            'caption_id'          => $this->caption_id,
            'wise_caption_id'     => $this->wise_caption_id,
            'man_of_the_match_id' => $this->man_of_the_match_id,

            'caption'          => MemberShortResource::make($this->whenLoaded('caption')),
            'wise_caption'     => MemberShortResource::make($this->whenLoaded('wiseCaption')),
            'man_of_the_match' => MemberShortResource::make($this->whenLoaded('manOfTheMatch')),
            'members'          => MemberShortResource::collection($this->whenLoaded('members')),
        ];
    }
}
