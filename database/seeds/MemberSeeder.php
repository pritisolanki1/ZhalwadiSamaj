<?php

use App\Models\Member;
use App\Traits\MemberTraits;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    use MemberTraits;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Member::factory()
            ->count(10)
            ->create()
            ->each(function ($member) {
                $member->assignRole('Member');
                $this->addUserLogin($member);
            });
    }
}
