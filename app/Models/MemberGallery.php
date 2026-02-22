<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use LaravelIdea\Helper\App\Models\_IH_MemberGallery_C;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MemberGallery extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('MemberGallery')->logUnguarded()->logOnlyDirty()->dontSubmitEmptyLogs();
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

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id')->withTrashed();
    }

    public static function GetAll($id = ''): _IH_MemberGallery_C|array|self|null
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
                $imageUrl = getImageUrlIfExists($image, Config::get('general.image_path.member_gallery.images'));
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
                $videoPath = public_path(Config::get('general.image_path.member_gallery.videos') . $video);
                if (\Illuminate\Support\Facades\File::exists($videoPath)) {
                    $result[] = url(Config::get('general.image_path.member_gallery.videos') . $video);
                }
            }
        }

        return $result;
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
