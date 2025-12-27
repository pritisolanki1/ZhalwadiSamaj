<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\GameResultStoreRequest;
use App\Http\Resources\GameResultResource;
use App\Models\Game;
use App\Models\GameResult;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @OA\Tag(
 *     name="Game Results",
 *     description="API endpoints for game result management"
 * )
 */
class GameResultController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/game_result/get_all",
     *     tags={"Game Results"},
     *     summary="Get All Game Results",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $gameResults = GameResult::loadRelationships()->get();

        return GameResultResource::collection($gameResults);
    }

    public function store(GameResultStoreRequest $request): Response|JsonResponse
    {
        $insertField = $request->except('member_id');
        $gameResult = GameResult::create($insertField);
        $gameResult->members()->sync($request->member_id);

        return $this->successResponse('Game Result Created Successfully.', GameResultResource::make($gameResult), 201);
    }

    public function update(GameResultStoreRequest $request, $id): Response|JsonResponse
    {
        $gameResult = GameResult::find($id);
        if (!$gameResult) {
            return $this->errorResponse('Game result not found', null, 404);
        }

        $UpdateField = $request->except('member_id');
        if (!empty($UpdateField['image'])) {
            unset($UpdateField['image']);
        }

        $gameResult->fill($UpdateField)->save();
        $gameResult->members()->sync($request->member_id);

        return $this->successResponse('Game Result Update Successfully.', [], 201);
    }

    public function destroy($id): Response|JsonResponse
    {
        $gameResult = GameResult::find($id);
        if (!$gameResult) {
            return $this->errorResponse('Game result not found it is already been deleted', null, 404);
        }

        $gameResult->members()->detach();

        $gameResult->delete();

        return $this->successResponse('Game result deleted');
    }

    /**
     * @throws Throwable
     */
    public function upload_image(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!GameResult::find($id)) {
                throw new Exception('Game Result not found');
            }
            $request->validate([
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            ]);

            $insertFiled['image'] = '';

            if ($request->hasFile('images')) {
                $image = $request->file('images');
                $name = md5(RandomStringGenerator(16) . time()) . '.' . $image->extension();
                $image->move(public_path(Config::get('general.image_path.game_result.image')), $name);
                $insertFiled['image'] = $name;
            }

            $gameResult = GameResult::findOrFail($id);
            $gameResult->image = $insertFiled['image'];
            $gameResult->save();
            DB::commit();

            return $this->successResponse('Records updated successfully.', Game::GetAll($gameResult->game_id));
        } catch (Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage());
        }
    }
}
