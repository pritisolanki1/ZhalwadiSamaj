<?php

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipPuzzlesTable extends Migration
{
    public function up(): void
    {
        Schema::create('zip_puzzles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('grid_data');
            $table->json('solution_path');
            $table->string('answer_word');
            $table->date('puzzle_date')->unique();
            $table->string('difficulty')->default('medium');
            $table->timestamps();

            $table->index('puzzle_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zip_puzzles');
    }
}
