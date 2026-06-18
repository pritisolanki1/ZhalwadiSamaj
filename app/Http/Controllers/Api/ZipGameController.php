<?php

namespace App\Http\Controllers\Api;

use App\Models\ZipPuzzle;
use App\Models\ZipGameResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZipGameController extends ApiController
{
    public function todayPuzzle()
    {
        $puzzle = ZipPuzzle::where('puzzle_date', today())->first();
        if (!$puzzle) {
            return $this->errorResponse('No puzzle available for today.', null, 404);
        }

        $userId = Auth::user()->id;
        $existingResult = ZipGameResult::where('user_id', $userId)
            ->where('puzzle_id', $puzzle->id)
            ->first();

        return $this->successResponse('Today\'s puzzle retrieved.', [
            'id' => $puzzle->id,
            'grid_size' => $puzzle->grid_size,
            'grid_numbers' => $puzzle->grid_numbers,
            'puzzle_date' => $puzzle->puzzle_date->format('Y-m-d'),
            'difficulty' => $puzzle->difficulty,
            'has_played' => $existingResult !== null,
            'my_result' => $existingResult ? [
                'is_correct' => $existingResult->is_correct,
                'completion_time_seconds' => $existingResult->completion_time_seconds,
                'completed_at' => $existingResult->completed_at?->toIso8601String(),
            ] : null,
        ]);
    }

    public function submitResult(Request $request)
    {
        $request->validate([
            'puzzle_id' => 'required|exists:zip_puzzles,id',
            'path_submitted' => 'required|array',
            'path_submitted.*' => 'required|array|size:2',
            'completion_time_seconds' => 'required|integer|min:1',
        ]);

        $userId = Auth::user()->id;
        $puzzle = ZipPuzzle::findOrFail($request->puzzle_id);

        $existing = ZipGameResult::where('user_id', $userId)
            ->where('puzzle_id', $puzzle->id)
            ->first();

        if ($existing) {
            return $this->errorResponse('You have already submitted a result for this puzzle.', null, 422);
        }

        $gridSize = $puzzle->grid_size;
        $totalCells = $gridSize * $gridSize;
        $solutionPath = $puzzle->solution_path;
        $pathSubmitted = $request->path_submitted;
        $isCorrect = $this->validatePath($pathSubmitted, $solutionPath, $gridSize);

        $result = ZipGameResult::create([
            'user_id' => $userId,
            'puzzle_id' => $puzzle->id,
            'completion_time_seconds' => $request->completion_time_seconds,
            'path_submitted' => $pathSubmitted,
            'is_correct' => $isCorrect,
            'completed_at' => now(),
        ]);

        $rank = null;
        if ($isCorrect) {
            $rank = ZipGameResult::where('puzzle_id', $puzzle->id)
                ->where('is_correct', true)
                ->where('completion_time_seconds', '<', $result->completion_time_seconds)
                ->count() + 1;
        }

        return $this->successResponse($isCorrect ? 'Correct! Puzzle solved.' : 'Incorrect path.', [
            'is_correct' => $isCorrect,
            'correct_solution' => $solutionPath,
            'completion_time_seconds' => $result->completion_time_seconds,
            'rank' => $rank,
        ]);
    }

    public function leaderboard()
    {
        $todayPuzzle = ZipPuzzle::where('puzzle_date', today())->first();
        if (!$todayPuzzle) {
            return $this->successResponse('No puzzle today.', [
                'leaderboard' => [],
                'total_players' => 0,
                'user_rank' => null,
            ]);
        }

        $results = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
            ->where('is_correct', true)
            ->orderBy('completion_time_seconds')
            ->with('user')
            ->take(20)
            ->get();

        $userId = Auth::user()->id;
        $userRank = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
            ->where('is_correct', true)
            ->where('user_id', $userId)
            ->first();

        $userRankPosition = null;
        if ($userRank) {
            $userRankPosition = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
                ->where('is_correct', true)
                ->where('completion_time_seconds', '<', $userRank->completion_time_seconds)
                ->count() + 1;
        }

        $totalPlayers = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
            ->where('is_correct', true)
            ->count();

        $leaderboard = [];
        foreach ($results as $i => $r) {
            $leaderboard[] = [
                'rank' => $i + 1,
                'user_id' => $r->user_id,
                'user_name' => optional($r->user)->name ?? 'Unknown',
                'user_avatar' => optional($r->user)->avatar ?? null,
                'completion_time_seconds' => $r->completion_time_seconds,
                'completed_at' => $r->completed_at?->toIso8601String(),
            ];
        }

        return $this->successResponse('Leaderboard retrieved.', [
            'leaderboard' => $leaderboard,
            'total_players' => $totalPlayers,
            'user_rank' => $userRankPosition,
        ]);
    }

    public function myHistory()
    {
        $userId = Auth::user()->id;
        $results = ZipGameResult::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->with('puzzle')
            ->take(30)
            ->get();

        $history = [];
        foreach ($results as $r) {
            $history[] = [
                'id' => $r->id,
                'puzzle_id' => $r->puzzle_id,
                'puzzle_date' => optional($r->puzzle)->puzzle_date?->format('Y-m-d'),
                'completion_time_seconds' => $r->completion_time_seconds,
                'is_correct' => $r->is_correct,
                'completed_at' => $r->completed_at,
            ];
        }

        return $this->successResponse('History retrieved.', $history);
    }

    private function validatePath(array $path, array $solution, int $gridSize): bool
    {
        $totalCells = $gridSize * $gridSize;

        if (count($path) !== $totalCells) {
            return false;
        }

        $visited = [];
        foreach ($path as $i => $step) {
            $row = $step[0];
            $col = $step[1];

            if ($row < 0 || $row >= $gridSize || $col < 0 || $col >= $gridSize) {
                return false;
            }

            $key = $row . ',' . $col;
            if (isset($visited[$key])) {
                return false;
            }
            $visited[$key] = true;

            if ($i > 0) {
                $prevRow = $path[$i - 1][0];
                $prevCol = $path[$i - 1][1];
                $dr = abs($row - $prevRow);
                $dc = abs($col - $prevCol);
                if ($dr + $dc !== 1) {
                    return false;
                }
            }
        }

        foreach ($solution as $i => $step) {
            if ($path[$i][0] !== $step[0] || $path[$i][1] !== $step[1]) {
                return false;
            }
        }

        return true;
    }
}
