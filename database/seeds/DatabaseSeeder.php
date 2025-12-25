<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ZoneSeeder::class,
            KuldeviSeeder::class,
            NativePlacesSeeder::class,
            UserSeeder::class,
            MemberSeeder::class,
        ]);
    }
}
