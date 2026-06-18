<?php

namespace App\Http\Controllers\Api;

use App\Models\Member;
use App\Models\ZipGameResult;
use App\Models\ZipPuzzle;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZipGameController extends ApiController
{
    private function getUser(): Member
    {
        return Auth::user();
    }

    private function todayPuzzle(): ZipPuzzle
    {
        return ZipPuzzle::where('puzzle_date', now()->toDateString())->firstOrFail();
    }

    public function today(): JsonResponse
    {
        try {
            $puzzle = $this->todayPuzzle();
            $user = $this->getUser();

            $existingResult = ZipGameResult::where('user_id', $user->id)
                ->where('puzzle_id', $puzzle->id)
                ->first();

            return $this->successResponse('Today\'s puzzle retrieved.', [
                'puzzle' => [
                    'id'          => $puzzle->id,
                    'grid_data'   => $puzzle->grid_data,
                    'grid_size'   => count($puzzle->grid_data),
                    'answer_word' => $puzzle->answer_word,
                    'difficulty'  => $puzzle->difficulty,
                    'puzzle_date' => $puzzle->puzzle_date->format('Y-m-d'),
                ],
                'already_played' => $existingResult !== null,
                'my_result'      => $existingResult ? [
                    'completion_time_seconds' => $existingResult->completion_time_seconds,
                    'completed_at'            => $existingResult->completed_at,
                    'attempts'                => $existingResult->attempts,
                ] : null,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('No puzzle available for today.', null, 404);
        }
    }

    public function submit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'puzzle_id'              => 'required|uuid|exists:zip_puzzles,id',
                'completion_time_seconds' => 'nullable|integer|min:0',
                'path_taken'             => 'required|array',
                'path_taken.*'           => 'array',
            ]);

            $user = $this->getUser();
            $puzzle = ZipPuzzle::findOrFail($request->puzzle_id);

            $existing = ZipGameResult::where('user_id', $user->id)
                ->where('puzzle_id', $puzzle->id)
                ->first();

            if ($existing) {
                return $this->errorResponse('You have already submitted a result for today\'s puzzle.', null, 409);
            }

            $solutionPath = $puzzle->solution_path;
            $submittedPath = $request->path_taken;

            $isCorrect = $this->validatePath($submittedPath, $solutionPath);

            if (!$isCorrect) {
                return $this->errorResponse('The submitted path does not match the solution.', null, 422);
            }

            $result = ZipGameResult::create([
                'user_id'                 => $user->id,
                'puzzle_id'               => $puzzle->id,
                'completion_time_seconds' => $request->completion_time_seconds,
                'path_taken'              => $submittedPath,
                'completed_at'            => now(),
                'attempts'                => 1,
            ]);

            return $this->successResponse('Puzzle completed successfully!', [
                'result' => [
                    'id'                      => $result->id,
                    'completion_time_seconds' => $result->completion_time_seconds,
                    'completed_at'            => $result->completed_at,
                ],
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function leaderboard(): JsonResponse
    {
        try {
            $puzzle = $this->todayPuzzle();

            $results = ZipGameResult::where('puzzle_id', $puzzle->id)
                ->whereNotNull('completion_time_seconds')
                ->with('member')
                ->orderBy('completion_time_seconds', 'asc')
                ->take(20)
               ->get();

            $user = $this->getUser();

            $userResult = ZipGameResult::where('user_id', $user->id)
                ->where('puzzle_id', $puzzle->id)
                ->first();

            $userRank = null;
            if ($userResult && $userResult->completion_time_seconds !== null) {
                $userRank = ZipGameResult::where('puzzle_id', $puzzle->id)
                        ->whereNotNull('completion_time_seconds')
                        ->where('completion_time_seconds', '<', $userResult->completion_time_seconds)
                        ->count() + 1;
            }

            $leaderboard = [];
            foreach ($results as $i => $result) {
                $member = $result->member;
                $leaderboard[] = [
                    'rank'                    => $i + 1,
                    'user_id'                 => $member->id,
                    'user_name'               => $member->name['en'] ?? 'Unknown',
                    'user_avatar'             => $member->avatar ?? '',
                    'completion_time_seconds' => $result->completion_time_seconds,
                    'is_current_user'         => $member->id === $user->id,
                ];
            }

            return $this->successResponse('Leaderboard retrieved.', [
                'leaderboard'       => $leaderboard,
                'user_rank'         => $userRank,
                'total_players'     => ZipGameResult::where('puzzle_id', $puzzle->id)->count(),
                'user_result'       => $userResult ? [
                    'completion_time_seconds' => $userResult->completion_time_seconds,
                    'completed_at'            => $userResult->completed_at,
                ] : null,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function myHistory(): JsonResponse
    {
        try {
            $user = $this->getUser();

            $results = ZipGameResult::where('user_id', $user->id)
                ->with('puzzle')
                ->orderBy('created_at', 'desc')
                ->get();

            $history = $results->map(function ($r) {
                return [
                    'id'                      => $r->id,
                    'puzzle_id'               => $r->puzzle_id,
                    'puzzle_date'             => $r->puzzle->puzzle_date->format('Y-m-d'),
                    'answer_word'             => $r->puzzle->answer_word,
                    'completion_time_seconds' => $r->completion_time_seconds,
                    'completed_at'            => $r->completed_at,
                    'attempts'                => $r->attempts,
                ];
            });

            return $this->successResponse('History retrieved.', $history);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function validatePath(array $submitted, array $solution): bool
    {
        if (count($submitted) !== count($solution)) {
            return false;
        }

        foreach ($submitted as $i => $step) {
            if (!isset($step[0]) || !isset($step[1]) ||
                !isset($solution[$i][0]) || !isset($solution[$i][1])) {
                return false;
            }
            if ((int) $step[0] !== (int) $solution[$i][0] ||
                (int) $step[1] !== (int) $solution[$i][1]) {
                return false;
            }
        }

        return true;
    }
}
