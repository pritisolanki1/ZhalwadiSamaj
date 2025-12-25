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
        foreach ($images as &$image) {
            if (isset($image) && !empty($image)) {
                $image = url('/image/0/0/' . Config::get('general.image_path.member_gallery.images') . $image);
            }
        }

        return $images;
    }

    public function getVideosAttribute($value)
    {
        $videos = jsonDecode($value);
        foreach ($videos as &$video) {
            if (isset($video) && !empty($video)) {
                $video = url('/image/0/0/' . Config::get('general.image_path.member_gallery.images') . $video);
            }
        }

        return $videos;
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
