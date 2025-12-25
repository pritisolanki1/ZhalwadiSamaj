<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Msf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'msf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan migrate:fresh --seed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Artisan::call('migrate:fresh --seed');
        Artisan::call('key:generate --force');
        Artisan::call('passport:install --force');
    }
}
