<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppVersionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('latest_version')->default('1.2');
            $table->string('minimum_supported_version')->default('1.2');
            $table->boolean('force_update')->default(false);
            $table->text('update_message')->nullable();
            $table->string('play_store_url')->nullable();
            $table->timestamps();
        });

        DB::table('app_versions')->insert([
            'latest_version'             => '1.2',
            'minimum_supported_version'  => '1.2',
            'force_update'               => false,
            'update_message'             => 'A new version is available. Please update to continue.',
            'play_store_url'             => 'https://play.google.com/store/apps/details?id=com.zalawadi.app',
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
}
