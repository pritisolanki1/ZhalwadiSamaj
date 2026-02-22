<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

trait BusinessAttributes
{
    public function getLogoAttribute($value)
    {
        $images = jsonDecode($value);
        if (!is_array($images)) {
            return [];
        }
        
        $result = [];
        foreach ($images as $image) {
            if (isset($image) && !empty($image)) {
                $imageUrl = getImageUrlIfExists($image, Config::get('general.image_path.business.logo'));
                if (!empty($imageUrl)) {
                    $result[] = $imageUrl;
                }
            }
        }

        return $result;
    }

    public function getSliderAttribute($value)
    {
        $images = jsonDecode($value);
        if (!is_array($images)) {
            return [];
        }
        
        $result = [];
        foreach ($images as $image) {
            if (isset($image) && !empty($image)) {
                $imageUrl = getImageUrlIfExists($image, Config::get('general.image_path.business.slider'));
                if (!empty($imageUrl)) {
                    $result[] = $imageUrl;
                }
            }
        }

        return $result;
    }

    public function getGalleryAttribute($value)
    {
        $images = jsonDecode($value);
        if (!is_array($images)) {
            return [];
        }
        
        $result = [];
        foreach ($images as $image) {
            if (isset($image) && !empty($image)) {
                $imageUrl = getImageUrlIfExists($image, Config::get('general.image_path.business.gallery'));
                if (!empty($imageUrl)) {
                    $result[] = $imageUrl;
                }
            }
        }

        return $result;
    }
}
