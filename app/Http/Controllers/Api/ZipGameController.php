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
            $puzzle = ZipPuzzle::generateForDate(today());
        }

        $userId = Auth::user()->id;
        $existingResult = ZipGameResult::where('user_id', $userId)
            ->where('puzzle_id', $puzzle->id)
            ->first();

        $stats = $this->getUserStats($userId);

        $grid = $this->buildGrid($puzzle->grid_size, $puzzle->grid_numbers);

        return $this->successResponse('Today\'s puzzle retrieved.', [
            'id' => $puzzle->id,
            'grid_size' => $puzzle->grid_size,
            'grid' => $grid,
            'grid_numbers' => $puzzle->grid_numbers,
            'puzzle_date' => $puzzle->puzzle_date->format('Y-m-d'),
            'difficulty' => $puzzle->difficulty,
            'server_date' => today()->format('Y-m-d'),
            'has_played' => $existingResult !== null,
            'my_result' => $existingResult ? [
                'is_correct' => $existingResult->is_correct,
                'completion_time_seconds' => $existingResult->completion_time_seconds,
                'completed_at' => $existingResult->completed_at?->toIso8601String(),
            ] : null,
            'stats' => $stats,
        ]);
    }

    public function submitResult(Request $request)
    {
        $request->validate([
            'puzzle_id' => 'required|exists:zip_puzzles,id',
            'path_submitted' => 'required|array',
            'path_submitted.*' => 'required|array|size:2',
            'completion_time_seconds' => 'required|integer|min:0',
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
        $isCorrect = $this->validatePath($pathSubmitted, $solutionPath, $gridSize, $puzzle->grid_numbers);

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
                'user_result' => null,
            ]);
        }

        $results = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
            ->where('is_correct', true)
            ->orderBy('completion_time_seconds')
            ->with('user')
            ->get();

        $userId = Auth::user()->id;
        $userResult = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
            ->where('user_id', $userId)
            ->first();

        $userRankPosition = null;
        if ($userResult && $userResult->is_correct) {
            $userRankPosition = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
                ->where('is_correct', true)
                ->where('completion_time_seconds', '<', $userResult->completion_time_seconds)
                ->count() + 1;
        }

        $totalPlayers = ZipGameResult::where('puzzle_id', $todayPuzzle->id)
            ->where('is_correct', true)
            ->count();

        $leaderboard = [];
        $currentRank = 0;
        $prevTime = null;
        foreach ($results as $i => $r) {
            if ($prevTime === null || (float) $r->completion_time_seconds !== (float) $prevTime) {
                $currentRank++;
            }
            $prevTime = $r->completion_time_seconds;

            $user = $r->user;
            $nameArr = $user ? $user->name : null;
            $userName = is_array($nameArr) ? ($nameArr['en'] ?? 'Unknown') : ($nameArr ?? 'Unknown');

            $leaderboard[] = [
                'rank' => $currentRank,
                'user_id' => $r->user_id,
                'user_name' => $userName,
                'user_avatar' => $user ? ($user->avatar ?? '') : '',
                'completion_time_seconds' => $r->completion_time_seconds,
                'completed_at' => $r->completed_at?->toIso8601String(),
            ];
        }

        return $this->successResponse('Leaderboard retrieved.', [
            'leaderboard' => $leaderboard,
            'total_players' => $totalPlayers,
            'user_rank' => $userRankPosition,
            'user_result' => $userResult ? [
                'user_id' => (string) $userResult->user_id,
                'is_correct' => $userResult->is_correct,
                'completion_time_seconds' => $userResult->completion_time_seconds,
                'completed_at' => $userResult->completed_at?->toIso8601String(),
            ] : null,
            'current_user_id' => (string) $userId,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
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

    public function myStats()
    {
        $userId = Auth::user()->id;
        $stats = $this->getUserStats($userId);
        return $this->successResponse('Stats retrieved.', $stats);
    }

    /**
     * Compute aggregate statistics for a user.
     */
    private function getUserStats($userId): array
    {
        $allResults = ZipGameResult::where('user_id', $userId)->get();
        $totalPlayed = $allResults->count();
        $correctResults = $allResults->where('is_correct', true);
        $totalSolved = $correctResults->count();

        $completionTimes = $correctResults->pluck('completion_time_seconds')->filter();

        return [
            'total_played' => $totalPlayed,
            'total_solved' => $totalSolved,
            'completion_rate' => $totalPlayed > 0 ? round(($totalSolved / $totalPlayed) * 100, 1) : 0,
            'average_time_seconds' => $completionTimes->isNotEmpty() ? round($completionTimes->average()) : 0,
            'best_time_seconds' => $completionTimes->isNotEmpty() ? $completionTimes->min() : 0,
            'streak' => $this->computeStreak($userId),
        ];
    }

    /**
     * Compute current streak from consecutive daily completions.
     */
    private function computeStreak($userId): int
    {
        $correctResults = ZipGameResult::where('user_id', $userId)
            ->where('is_correct', true)
            ->with('puzzle')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($correctResults->isEmpty()) return 0;

        $dates = [];
        foreach ($correctResults as $r) {
            if ($r->puzzle && $r->puzzle->puzzle_date) {
                $dates[] = $r->puzzle->puzzle_date->format('Y-m-d');
            }
        }

        $dates = array_unique($dates);
        rsort($dates);

        $streak = 0;
        $expected = today()->format('Y-m-d');
        foreach ($dates as $date) {
            if ($date === $expected) {
                $streak++;
                $expected = date('Y-m-d', strtotime($expected . ' -1 day'));
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Build a full 2-D grid matrix from grid_numbers.
     * Empty cells are 0; numbered waypoints are placed at their positions.
     */
    private function buildGrid(int $gridSize, array $gridNumbers): array
    {
        $grid = array_fill(0, $gridSize, array_fill(0, $gridSize, 0));
        foreach ($gridNumbers as $gn) {
            $grid[$gn['row']][$gn['col']] = $gn['number'];
        }
        return $grid;
    }

    /**
     * Validate the submitted path (LinkedIn-style).
     * Path must:
     * 1. Cover every cell exactly once
     * 2. Be a continuous path (adjacent moves, no diagonals)
     * 3. Visit numbered waypoints in correct order
     * 4. Stay within bounds
     * 5. Never overlap/cross itself
     *
     * Does NOT compare against a stored solution — any valid Hamiltonian
     * path through the waypoints is accepted.
     */
    private function validatePath(array $path, array $solution, int $gridSize, array $gridNumbers): bool
    {
        $totalCells = $gridSize * $gridSize;

        // 1. Must cover every cell exactly once
        if (count($path) !== $totalCells) {
            return false;
        }

        // 2-5. Check boundaries, uniqueness, adjacency
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

        // 3. Verify waypoints are visited in correct order
        $waypointIndex = 0;
        $sortedWaypoints = collect($gridNumbers)->sortBy('number')->values();
        foreach ($path as $i => $step) {
            if ($waypointIndex < count($sortedWaypoints)) {
                $wp = $sortedWaypoints[$waypointIndex];
                if ($step[0] == $wp['row'] && $step[1] == $wp['col']) {
                    $waypointIndex++;
                }
            }
        }
        if ($waypointIndex !== count($sortedWaypoints)) {
            return false;
        }

        return true;
    }
}
