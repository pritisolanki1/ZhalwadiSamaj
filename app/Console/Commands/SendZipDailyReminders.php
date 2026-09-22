<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\ZipGameResult;
use App\Models\ZipPuzzle;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendZipDailyReminders extends Command
{
    protected $signature = 'zip:send-daily-reminders
        {--dry-run : Calculate and report counts without sending any notification}';

    protected $description = 'Send the daily Zip Puzzle reminder to members who have not played today';

    private const TITLE = '🎮 Daily Zip Challenge';

    private const BODY = 'Today\'s Zip Puzzle is waiting for you. Play now and see your rank on the leaderboard!';

    private const TYPE = 'zip_daily';

    public function handle(FirebaseNotificationService $firebase): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Daily Zip Reminder');

        $puzzle = $this->todayPuzzle();

        if (!$puzzle) {
            $this->warn('No puzzle available for today; reminder skipped.');
            Log::info('Zip daily reminder skipped: no puzzle for today', [
                'puzzle_date' => today()->format('Y-m-d'),
            ]);

            return self::FAILURE;
        }

        $this->info('Puzzle ID: ' . $puzzle->id);
        $this->info('Puzzle Date: ' . $puzzle->puzzle_date->format('Y-m-d'));

        $candidates = $this->eligibleMembers($puzzle);

        $playedIds = ZipGameResult::where('puzzle_id', $puzzle->id)->pluck('user_id')->all();
        $playedSet = array_flip($playedIds);

        $missingToken = 0;
        $alreadyPlayed = 0;
        $toNotify = [];

        foreach ($candidates as $member) {
            if (empty($member->device_token)) {
                $missingToken++;
                continue;
            }

            if (isset($playedSet[$member->id])) {
                $alreadyPlayed++;
                continue;
            }

            $toNotify[] = $member;
        }

        $this->info('Eligible members: ' . $candidates->count());
        $this->info('Already played: ' . $alreadyPlayed);
        $this->info('Missing token: ' . $missingToken);
        $this->info('Notifications attempted: ' . count($toNotify));

        if ($dryRun) {
            $this->info('Dry run: no notifications sent.');
            Log::info('Zip daily reminder dry run', [
                'puzzle_id' => $puzzle->id,
                'puzzle_date' => $puzzle->puzzle_date->format('Y-m-d'),
                'eligible' => $candidates->count(),
                'already_played' => $alreadyPlayed,
                'missing_token' => $missingToken,
                'attempted' => count($toNotify),
            ]);

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;
        $finalAlreadyPlayed = $alreadyPlayed;
        $invalidTokens = [];

        foreach ($toNotify as $member) {
            // Duplicate protection: re-check the authoritative game state
            // immediately before sending in case the scheduler run again or
            // the member played while the command was executing.
            if (ZipGameResult::where('user_id', $member->id)->where('puzzle_id', $puzzle->id)->exists()) {
                $finalAlreadyPlayed++;
                continue;
            }

            try {
                $response = $firebase->sendPushNotification(
                    $member->device_token,
                    self::TITLE,
                    self::BODY,
                    ['type' => self::TYPE]
                );

                if ($response['success'] ?? false) {
                    $sent++;
                    continue;
                }

                $failed++;

                if ($response['invalid_token'] ?? false) {
                    $invalidTokens[] = $member->device_token;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Zip daily reminder send failed', [
                    'message' => $e->getMessage(),
                    'puzzle_id' => $puzzle->id,
                ]);
            }
        }

        if (!empty($invalidTokens)) {
            Member::whereIn('device_token', $invalidTokens)->update(['device_token' => null]);
        }

        $this->info('Already played (final): ' . $finalAlreadyPlayed);
        $this->info('Notifications sent: ' . $sent);
        $this->info('Failed: ' . $failed);

        Log::info('Zip daily reminder dispatched', [
            'puzzle_id' => $puzzle->id,
            'puzzle_date' => $puzzle->puzzle_date->format('Y-m-d'),
            'eligible' => $candidates->count(),
            'already_played' => $finalAlreadyPlayed,
            'missing_token' => $missingToken,
            'attempted' => count($toNotify),
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return self::SUCCESS;
    }

    /**
     * Resolve today's puzzle using the same server-side date convention used
     * by ZipGameController. Generates it lazily through the existing
     * ZipPuzzle generator when it does not exist yet.
     */
    private function todayPuzzle(): ?ZipPuzzle
    {
        $puzzle = ZipPuzzle::where('puzzle_date', today())->first();

        if ($puzzle) {
            return $puzzle;
        }

        try {
            return ZipPuzzle::generateForDate(today());
        } catch (\Throwable $e) {
            Log::warning('Zip daily reminder could not generate today\'s puzzle', [
                'message' => $e->getMessage(),
            ]);

            return ZipPuzzle::where('puzzle_date', today())->first();
        }
    }

    /**
     * Members who may receive the reminder. Same role assumption already used
     * by the existing Zip Puzzle routes (role:Member). Requires a usable
     * device token. Whether the member already played today is checked by the
     * caller against the authoritative zip_game_results state.
     */
    private function eligibleMembers(ZipPuzzle $puzzle): Collection
    {
        return Member::role('Member')
            ->whereNotNull('device_token')
            ->where('device_token', '<>', '')
            ->get(['id', 'device_token']);
    }
}