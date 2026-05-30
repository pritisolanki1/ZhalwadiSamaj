<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Illuminate\Console\Command;

class ClearFirebaseDeviceTokens extends Command
{
    protected $signature = 'firebase:clear-device-tokens
        {--members : Clear member device tokens}
        {--users : Clear admin/sub-admin user device tokens}
        {--force : Actually clear tokens; without this option the command only reports counts}';

    protected $description = 'Clear stored Firebase device tokens from users and/or members.';

    public function handle(): int
    {
        $clearMembers = (bool) $this->option('members');
        $clearUsers = (bool) $this->option('users');

        if (!$clearMembers && !$clearUsers) {
            $clearMembers = true;
            $clearUsers = true;
        }

        $force = (bool) $this->option('force');
        $memberCount = 0;
        $userCount = 0;

        if ($clearMembers) {
            $memberQuery = Member::whereNotNull('device_token')->where('device_token', '!=', '');
            $memberCount = $memberQuery->count();

            if ($force) {
                $memberQuery->update(['device_token' => null]);
            }
        }

        if ($clearUsers) {
            $userQuery = User::whereNotNull('device_token')->where('device_token', '!=', '');
            $userCount = $userQuery->count();

            if ($force) {
                $userQuery->update(['device_token' => null]);
            }
        }

        $this->info(($force ? 'Cleared' : 'Found') . ' Firebase device tokens.');
        $this->line('Members: ' . $memberCount);
        $this->line('Users: ' . $userCount);

        if (!$force) {
            $this->warn('Dry run only. Re-run with --force to clear these tokens.');
        }

        return self::SUCCESS;
    }
}
