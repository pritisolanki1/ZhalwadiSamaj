<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AnnouncementStoreRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Member;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class AnnouncementController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $length = $request->length ?: 20;
        $announcements = Announcement::query()->with([
            'members',
            'members.nativePlace',
            'members.headOfTheFamily',
        ])->where('status', $request->status ?? 1)->where('created_at', '>', now()->subYear())->latest();

        if (auth()->user()->hasRole('Member')) {
            $announcements->where(
                'created_at',
                '>',
                auth()->user()->created_at
            )->doesntHave('members')->orWhereHas('members', function ($q) {
                $q->where('id', auth()->id());
            });
        }
        $announcements = $announcements->paginate($length)->withQueryString();

        return AnnouncementResource::collection($announcements);
    }

    public function store(AnnouncementStoreRequest $request): Response|JsonResponse
    {
        try {
            $request->validated();
            $members = $request->members_id ? explode(',', $request->members_id) : [];
            $excludeMembers = $request->exclude_members_id ? explode(',', $request->exclude_members_id) : [];
            $insertFiled = $request->except('member');
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $name = md5(RandomStringGenerator(16) . time()) . '.' . $image->extension();
                $image->move(public_path(Config::get('general.image_path.announcement.image')), $name);
                $insertFiled['image'] = $name;
            }

            $announcement = Announcement::create($insertFiled);
            if (!empty($members) || !empty($excludeMembers)) {
                if (!empty($members)) {
                    $members = Member::whereNotnull('device_token')->where('device_token', '!=', '')->whereIn(
                        'id',
                        $members
                    )->get()->pluck('id')->toArray();
                } elseif (!empty($excludeMembers)) {
                    $members = Member::whereNotnull('device_token')->where('device_token', '!=', '')->whereNotIn(
                        'id',
                        $excludeMembers
                    )->get()->pluck('id')->toArray();
                }
                $announcement->members()->sync($members);
            }
            $this->sendNotice($announcement);

            return $this->successResponse('Announcement Created', AnnouncementResource::make($announcement), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(AnnouncementStoreRequest $request, $id): Response|JsonResponse
    {
        $request->validated();

        $announcement = Announcement::find($id);
        if (!$announcement->exists()) {
            throw new Exception('Announcement not found');
        }

        if ($announcement->status == 1) {
            throw new Exception("Announcement can't update now.");
        }

        $announcement->fill(Arr::except($request->all(), 'image'))->save();
        $announcement->fresh();
        $this->sendNotice($announcement);

        return $this->successResponse('Announcement updated', AnnouncementResource::make($announcement), 201);
    }

    public function destroy($id): Response|JsonResponse
    {
        try {
            $announcement = Announcement::find($id);
            if (!$announcement->exists()) {
                throw new Exception('Announcement not found or it is already been deleted');
            }
            $announcement->delete();

            return $this->successResponse('Announcement deleted', null, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function sendNotice(Announcement $announcement)
    {
        if ($announcement->status == 1) {
            sendNotice($announcement->title, $announcement->description, [
                'type' => 'announcement',
                'data' => $announcement->toArray(),
            ], $announcement->members->pluck('id')->toArray());
        }
    }
}
