<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTableName extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('user_galleries', 'member_galleries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_galleries', function (Blueprint $table) {
        });
    }
}
