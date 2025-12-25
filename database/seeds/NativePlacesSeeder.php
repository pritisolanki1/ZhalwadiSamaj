<?php

use App\Models\NativePlace;
use Illuminate\Database\Seeder;

class NativePlacesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NativePlace::factory()
            ->count(10)
            ->create();
    }
}
