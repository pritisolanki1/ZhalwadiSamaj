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
        foreach ($images as &$image) {
            if (isset($image) && !empty($image)) {
                $image = url('/image/0/0/' . Config::get('general.image_path.gallery_image.images') . $image);
            }
        }

        return $images;
    }

    public function getVideosAttribute($value)
    {
        $videos = jsonDecode($value);
        foreach ($videos as &$video) {
            if (isset($video) && !empty($video)) {
                $video = url(Config::get('general.image_path.gallery_image.videos') . $video);
            }
        }

        return $videos;
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
