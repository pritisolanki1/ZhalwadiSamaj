<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Game */
class GameResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'game_name'            => $this->game_name,
            'game_type'            => $this->game_type,
            'year'                 => $this->year,
            'man_of_the_series_id' => $this->man_of_the_series_id,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,

            'game_results' => GameResultResource::collection($this->whenLoaded('gameResults')),
        ];
    }
}
