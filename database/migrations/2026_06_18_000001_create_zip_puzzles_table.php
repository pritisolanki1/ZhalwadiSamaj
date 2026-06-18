<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipPuzzlesTable extends Migration
{
    public function up(): void
    {
        Schema::create('zip_puzzles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('grid_size');
            $table->json('grid_numbers');
            $table->json('solution_path');
            $table->date('puzzle_date')->unique();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->timestamps();

            $table->index('puzzle_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zip_puzzles');
    }
}
