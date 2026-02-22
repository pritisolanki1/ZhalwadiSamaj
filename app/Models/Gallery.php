<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Gallery extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Gallery')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs()->dontLogIfAttributesChangedOnly(['address']);
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'name'      => 'array',
        'address'   => 'array',
        'images'    => 'array',
        'videos'    => 'array',
        'longitude' => 'string',
        'latitude'  => 'string',
        'date'      => 'date',
    ];

    public static function GetAll($id = '')
    {
        $iRes = self::with(['galleryImages']);
        if (!empty($id)) {
            return $iRes->find($id);
        } else {
            $iRes = $iRes->latest()->get();
        }

        return $iRes;
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(GalleryImage::class, 'gallery_id', 'id')->orderBy('created_at', 'DESC');
    }

    public function getImagesAttribute($value)
    {
        $images = jsonDecode($value);
        if (!is_array($images)) {
            return [];
        }
        
        $result = [];
        foreach ($images as $image) {
            if (isset($image) && !empty($image)) {
                $imageUrl = getImageUrlIfExists($image, Config::get('general.image_path.gallery.images'));
                if (!empty($imageUrl)) {
                    $result[] = $imageUrl;
                }
            }
        }

        return $result;
    }

    public function getVideosAttribute($value)
    {
        $videos = jsonDecode($value);
        if (!is_array($videos)) {
            return [];
        }
        
        $result = [];
        foreach ($videos as $video) {
            if (isset($video) && !empty($video)) {
                $videoUrl = getImageUrlIfExists($video, Config::get('general.image_path.gallery.images'));
                if (!empty($videoUrl)) {
                    $result[] = $videoUrl;
                }
            }
        }

        return $result;
    }

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
