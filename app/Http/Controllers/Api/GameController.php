<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\GameStoreRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Games",
 *     description="API endpoints for game management"
 * )
 */
class GameController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/game/get_all",
     *     tags={"Games"},
     *     summary="Get All Games",
     *     description="Retrieve a list of all games",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Games list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Game list"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $game = Game::GetAll();
            $additional = [
                'status'  => 'Success',
                'message' => 'Game list',
            ];

            return GameResource::collection($game)->additional($additional);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/game/store",
     *     tags={"Games"},
     *     summary="Create New Game",
     *     description="Create a new game record",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"game_name", "game_type", "year"},
     *             @OA\Property(property="game_name", type="object",
     *                 @OA\Property(property="en", type="string", example="Cricket"),
     *                 @OA\Property(property="gu", type="string", example="ક્રિકેટ")
     *             ),
     *             @OA\Property(property="game_type", type="string", example="sports"),
     *             @OA\Property(property="year", type="integer", example=2024)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Game created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Game Created Successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(GameStoreRequest $request): Response|JsonResponse
    {
        try {
            $game = Game::create($request->all());

            return $this->successResponse('Game Created Successfully.', $game, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(GameStoreRequest $request, $id): Response|JsonResponse
    {
        try {
            if (!Game::find($id)) {
                return $this->errorResponse('game not found', null, 404);
            }
            $UpdateField = $request->all();
            Game::find($id)->fill($UpdateField)->save();
            $iRes = Game::GetAll($id);

            return $this->successResponse('game Updated', $iRes, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id): Response|JsonResponse
    {
        try {
            $data = Game::find($id)->delete();
            if (!$data) {
                return $this->errorResponse('Game not found it is already been deleted', $data, 400);
            } else {
                return $this->successResponse('Game deleted', null, 201);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
