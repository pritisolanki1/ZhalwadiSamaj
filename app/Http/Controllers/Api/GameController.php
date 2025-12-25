<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\GameStoreRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GameController extends ApiController
{
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
