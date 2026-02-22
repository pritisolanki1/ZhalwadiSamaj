<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait MemberAttributes
{
    // public function getNameEnAttribute()
    // {
    //     return $this->name->en;
    // }

    public function getFatherIdAttribute($value)
    {
        return singeValue($value);
    }

    public function setBirthDateAttribute($value): void
    {
        $this->attributes['birth_date'] = $value == null ? null : date_format(date_create($value), 'Y-m-d');
    }

    public function setExpireDateAttribute($value): void
    {
        $this->attributes['expire_date'] = $value == null ? null : date_format(date_create($value), 'Y-m-d');
    }

    public function getMotherIdAttribute($value)
    {
        return singeValue($value);
    }

    public function getRelationIdAttribute($value)
    {
        return singeValue($value);
    }

    public function getNativePlaceIdAttribute($value)
    {
        return singeValue($value);
    }

    public function getBloodGroupAttribute($value)
    {
        return singeValue($value);
    }

    public function getBirthDateAttribute($value): ?string
    {
        return $value == null ? null : date('d-m-Y', strtotime($value));
    }

    public function getExpireDateAttribute($value): ?string
    {
        return $value == null ? null : date('d-m-Y', strtotime($value));
    }

    public function getEmailAttribute($value)
    {
        return singeValue($value);
    }

    public function getPhoneAttribute($value)
    {
        return singeValue($value);
    }

    public function getAddressAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getOccupationAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getAvatarAttribute($value)
    {
        if (isset($value) && !empty($value)) {
            $contains = Str::contains($value, url(''));
            if ($contains) {
                return $value;
            } else {
                $imageUrl = getImageUrlIfExists($value, Config::get('general.image_path.member.avatar'));
                return $imageUrl ?: '';
            }
        }

        return '';
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
                $imageUrl = getImageUrlIfExists($image, Config::get('general.image_path.member.slider'));
                if (!empty($imageUrl)) {
                    $result[] = $imageUrl;
                }
            }
        }

        return $result;
    }

    public function getProfessionAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getProfessionTypeAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getWorkAddressAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getMosalAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getKuldeviAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getPasswordAttribute($value)
    {
        return singeValue($value);
    }

    public function getHeadOfTheFamilyIdAttribute($value)
    {
        return singeValue($value);
    }

    public function getFatherNameAttribute($value)
    {
        return jsonDecode($value);
    }

    public function getMotherNameAttribute($value)
    {
        return jsonDecode($value);
    }

    // public function native_place()
    // {
    //     return $this->belongsTo(NativePlace::class,'native_place_id');
    // }

    // public function education()
    // {
    //     return $this->hasMany(Result::class)->orderBy('class','asc');
    // }

    public function getStatusAttribute($value)
    {
        if ($value == '1') {
            $value = 'Active';
        } elseif ($value == '2') {
            $value = 'Draft';
        } elseif ($value == '0') {
            $value = 'Block';
        }

        return $value;
    }

    public function setStatusAttribute($value): string
    {
        if ($value == 'Active') {
            $value = 1;
        } elseif ($value == 'Draft') {
            $value = 2;
        } elseif ($value == 'Block') {
            $value = 0;
        }

        return $this->attributes['status'] = strtolower($value);
    }
}
