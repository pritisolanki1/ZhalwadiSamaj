<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TeamStoreRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class TeamController extends ApiController
{
    public function index(): JsonResponse
    {
        try {
            $Team = Team::GetAll();

            return $this->successResponse('Teams List', $Team);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(TeamStoreRequest $request): JsonResponse
    {
        try {
            if ($request->team_type['en'] == 'Management Team' || $request->team_type['en'] == 'Development Team') {
                $Team = Team::create($request->all());

                return $this->successResponse('Team Created', $Team, 201);
            } else {
                return $this->errorResponse('Please enter proper data and team type', null, 400);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(TeamStoreRequest $request, $id): JsonResponse
    {
        try {
            if (!Team::find($id)) {
                throw new Exception('Team not found');
            }

            $request->validated();
            $updatedFiled = $request->all();
            Team::find($id)->fill($updatedFiled)->save();
            $iRes = Team::GetAll($id);

            return $this->successResponse('Team Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($team_id): JsonResponse
    {
        try {
            if (!Team::find($team_id)->exists()) {
                return $this->errorResponse('team not found', null, 400);
            }
            Team::find($team_id)->delete();

            return $this->successResponse('team deleted', null, 201);

            // $img_path=app_path("image/Team/avatar/{$team->avatar}");
            // if ($img_path)
            // {
            //     //File::delete($image_path);
            //     echo "yes";
            //     //    unlink($img_path);
            // }
            // else
            // {
            //     echo "no";
            // }
            // //$team->delete();
            // dd($team->avatar);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function upload_image(Request $request, $id): JsonResponse
    {
        try {
            if (!Team::find($id)) {
                throw new Exception('Team not found');
            }
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            ]);

            $insertFiled['avatar'] = '';

            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $name = md5(RandomStringGenerator(16) . time()) . '.' . $avatar->extension();
                $avatar->move(public_path(Config::get('general.image_path.team.avatar')), $name);
                $insertFiled['avatar'] = $name;
            }
            $Team = Team::findOrFail($id);
            $Team->avatar = !empty($insertFiled['avatar']) ? $insertFiled['avatar'] : $Team->avatar;
            $Team->save();

            return $this->successResponse('Records updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
