<?php

use App\Models\Kuldevi;
use Illuminate\Database\Seeder;

class KuldeviSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kuldevi::factory()
            ->count(10)
            ->create();
    }
}
