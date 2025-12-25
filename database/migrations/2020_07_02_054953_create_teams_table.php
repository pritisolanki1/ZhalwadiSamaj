<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('member_id')->nullable();
            $table->json('team_type')->comment('multi language name in json_array');
            $table->string('admin_type')->nullable();
            // $table->json('name')->comment('multi language name in json_array');
            // $table->json('authority_types')->nullable();
            // $table->string('phone')->nullable();
            // $table->string('email')->nullable();
            $table->text('avatar')->nullable();
            $table->enum('status', [
                '0',
                '1',
                '2',
            ])->comment('0=Block, 1=Active, 2=Draft');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
}
