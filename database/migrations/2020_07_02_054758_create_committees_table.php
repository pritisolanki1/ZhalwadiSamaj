<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommitteesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('member_id')->nullable();
            $table->uuid('zone_id')->nullable();
            $table->json('name')->comment('multi language name in json_array');
            $table->json('authority_types')->nullable();
            // $table->enum('gender', ['Male', 'Female']);
            // $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            // $table->string('email')->nullable();
            // $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            // $table->json('address')->nullable();
            // $table->text('avatar')->nullable();
            $table->enum('status', [
                '0',
                '1',
                '2',
            ])->comment('0=Block, 1=Active, 2=Draft');
            $table->json('designation')->comment('multi language name in json_array');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
}
