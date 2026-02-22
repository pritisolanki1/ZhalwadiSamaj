<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GalleryImage extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('GalleryImage')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
    ];

    public static function GetAll($id = '')
    {
        $iRes = new self();
        if (!empty($id)) {
            return $iRes->find($id);
        } else {
            $iRes = $iRes->get();
        }

        return $iRes;
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
                $imageUrl = getImageUrlIfExists($image, Config::get('general.image_path.gallery_image.images'));
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
                // For videos, check if file exists
                $videoPath = public_path(Config::get('general.image_path.gallery_image.videos') . $video);
                if (\Illuminate\Support\Facades\File::exists($videoPath)) {
                    $result[] = url(Config::get('general.image_path.gallery_image.videos') . $video);
                }
            }
        }

        return $result;
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function deleteMedia(array $currentImageArray = [])
    {
        checkDeferenceDeleteMedia($currentImageArray, $this->images);
    }
}
