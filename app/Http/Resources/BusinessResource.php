<?php

namespace App\Http\Resources;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Business */
class BusinessResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'address'    => $this->address,
            'latitude'   => $this->latitude,
            'longitude'  => $this->longitude,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'website'    => $this->website,
            'about'      => $this->about,
            'partner_id' => $this->partner_id,
            'logo'       => $this->logo,
            'slider'     => $this->slider,
            'gallery'    => $this->gallery,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'jobs_count' => $this->jobs_count,
        ];
    }
}
