<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ReportStoreRequest;
use App\Http\Requests\ReportUpdateRequest;
use App\Models\GalleryImage;
use App\Models\MemberGallery;
use App\Models\Report;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @OA\Tag(
 *     name="Reports",
 *     description="API endpoints for report management"
 * )
 */
class ReportController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/reports",
     *     tags={"Reports"},
     *     summary="Get All Reports",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="length", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $length = $request->length > 0 ? $request->length : 10;
            // DB::enableQueryLog();
            $reports = Report::with([
                'reported_user:id,name,avatar,head_of_the_family_id',
                'member:id,name,avatar,head_of_the_family_id',
                'reportable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        GalleryImage::class  => ['gallery:id,name'],
                        MemberGallery::class => ['member:id,name,avatar,native_place_id,pancard'],
                    ]);
                },
            ])->whereDate('created_at', '>', now()->subYear())->orderBy('id', 'DESC')->paginate($length);

            // dd(DB::getQueryLog());
            return $this->successResponse('Report submited successfully.', $reports);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(ReportStoreRequest $request): JsonResponse
    {
        $report = Report::where('reportable_id', $request->id)->where(
            'report_user_id',
            auth()->user()->id
        )->where('value', $request->value)->first();
        if ($report) {
            return $this->errorResponse('You was already reported this image.');
        }

        $report = new Report();
        $report->value = $request->value;
        $report->report_user_id = auth()->user()->id;
        $report->report_user_notes = $request->notes;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = md5(RandomStringGenerator(16) . time()) . '.' . $image->extension();
            $image->move(public_path(Config::get('general.image_path.report.image')), $name);
            $report->image = $name;
        }
        $gallery = null;

        if ($request->type == 'gallery_image') {
            $gallery = GalleryImage::findOrFail($request->id);
        } elseif ($request->type == 'member_gallery') {
            $gallery = MemberGallery::findOrFail($request->id);
        }

        if ($gallery) {
            $report->member_id = $gallery->member_id;
            $gallery->reports()->save($report);
        }

        return $this->successResponse('Report submitted successfully.');
    }

    public function show(Report $report): void
    {
    }

    public function update(ReportUpdateRequest $request, Report $report): JsonResponse
    {
        $insertFiled['action_user_notes'] = $request->notes;
        $insertFiled['status'] = $request->status;

        $report = $report->fill($insertFiled)->save();

        return $this->successResponse('Report updated successfully.', $report, 201);
    }

    public function destroy(Report $report): JsonResponse
    {
        try {
            $data = $report->delete();
            if (!$data) {
                return $this->errorResponse('Report not found/it is already been deleted', $data, 400);
            } else {
                return $this->successResponse('Report deleted', null, 201);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
