<?php

namespace App\Http\Resources;

use App\Models\NativePlace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NativePlace */
class NativePlaceResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'native' => $this->native,
            //            'taluka'     => $this->taluka,
            //            'district'   => $this->district,
            //            'state'      => $this->state,
            //            'latitude'   => $this->latitude,
            //            'longitude'  => $this->longitude,
            //            'status'     => $this->status,
            //            'created_at' => $this->created_at,
            //            'updated_at' => $this->updated_at,
        ];
    }
}
