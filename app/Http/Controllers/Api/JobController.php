<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\JobStoreRequest;
use App\Models\Job;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class JobController extends ApiController
{
    public function index(): JsonResponse
    {
        try {
            $Job = Job::GetAll();

            return $this->successResponse('Jobs List', $Job);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(JobStoreRequest $request): JsonResponse
    {
        try {
            $request->validated();
            $Job = Job::create($request->all());

            return $this->successResponse('Job Created', $Job, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(JobStoreRequest $request, $id): JsonResponse
    {
        try {
            if (!Job::find($id)) {
                return $this->errorResponse('Job not found', null, 404);
            }
            $iData = $request->validated();
            $updatedFiled = $request->all();
            Job::find($id)->fill($updatedFiled)->save();
            $iRes = Job::GetAll($id);

            return $this->successResponse('Job Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $data = Job::find($id)->delete();
            if (!$data) {
                return $this->errorResponse('Job not found/it is already been deleted', $data, 400);
            } else {
                return $this->successResponse('Job deleted', null, 201);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function upload_image(Request $request, $id): JsonResponse
    {
        try {
            if (!Job::find($id)) {
                throw new Exception('Job not found');
            }
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            ]);

            $avatar = $request->file('avatar');
            $name = md5(RandomStringGenerator(16) . time()) . '.' . $avatar->extension();
            $avatar->move(public_path(Config::get('general.image_path.job.avatar')), $name);
            // dd($avatar);
            $Job = Job::find($id);
            $Job->avatar = $name;
            $Job->save();

            return $this->successResponse('Image updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
