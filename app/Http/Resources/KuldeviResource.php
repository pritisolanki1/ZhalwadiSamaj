<?php

namespace App\Http\Resources;

use App\Models\Kuldevi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Kuldevi */
class KuldeviResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            //            'created_at' => $this->created_at,
            //            'updated_at' => $this->updated_at,
        ];
    }
}
