<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('business_id')->nullable();
            $table->uuid('member_id')->nullable();
            $table->string('title', 50);
            $table->json('job_description');
            $table->text('avatar')->nullable();
            $table->string('city', 50);
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
        Schema::dropIfExists('jobs');
    }
}
