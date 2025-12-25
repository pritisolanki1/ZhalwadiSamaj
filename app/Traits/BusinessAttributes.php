<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

trait BusinessAttributes
{
    public function getLogoAttribute($value)
    {
        $images = jsonDecode($value);
        foreach ($images as &$image) {
            if (isset($image) && !empty($image)) {
                $image = url('/image/0/0/' . Config::get('general.image_path.business.logo') . $image);
            }
        }

        return $images;
    }

    public function getSliderAttribute($value)
    {
        $images = jsonDecode($value);
        foreach ($images as &$image) {
            if (isset($image) && !empty($image)) {
                $image = url('/image/0/0/' . Config::get('general.image_path.business.slider') . $image);
            }
        }

        return $images;
    }

    public function getGalleryAttribute($value)
    {
        $images = jsonDecode($value);
        foreach ($images as &$image) {
            if (isset($image) && !empty($image)) {
                $image = url('/image/0/0/' . Config::get('general.image_path.business.gallery') . $image);
            }
        }

        return $images;
    }
}
