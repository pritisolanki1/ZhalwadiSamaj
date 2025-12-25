<?php

namespace App\Http\Resources;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GalleryImage */
class GalleryImageResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'images'        => $this->images,
            'videos'        => $this->videos,
            'description'   => $this->description,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'reports_count' => $this->reports_count,

            'gallery_id' => $this->gallery_id,

            'gallery' => GalleryResource::make($this->whenLoaded('gallery')),
        ];
    }
}
