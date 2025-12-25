<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameResultsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_results', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('game_id')->require();
            $table->string('team_name')->nullable();
            $table->string('caption_id')->nullable();
            $table->string('wise_caption_id')->nullable();
            $table->string('man_of_the_match_id')->nullable();
            $table->string('image')->nullable();
            $table->integer('rank')->require();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_results');
    }
}
