<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipGameResultsTable extends Migration
{
    public function up(): void
    {
        Schema::create('zip_game_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('puzzle_id');
            $table->unsignedInteger('completion_time_seconds')->nullable();
            $table->json('path_taken')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(1);
            $table->timestamps();

            $table->foreign('puzzle_id')->references('id')->on('zip_puzzles')->onDelete('cascade');
            $table->unique(['user_id', 'puzzle_id']);
            $table->index('user_id');
            $table->index('puzzle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zip_game_results');
    }
}
