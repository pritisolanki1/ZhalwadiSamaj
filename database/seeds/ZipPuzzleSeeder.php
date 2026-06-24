<?php

namespace Database\Seeders;

use App\Models\ZipPuzzle;
use Illuminate\Database\Seeder;

class ZipPuzzleSeeder extends Seeder
{
    public function run(): void
    {
        for ($offset = 0; $offset < 14; $offset++) {
            $date = now()->addDays($offset);
            ZipPuzzle::generateForDate($date);
        }
    }
}
