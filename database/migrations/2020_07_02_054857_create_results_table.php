<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('member_id');
            $table->year('year');
            $table->string('class', 50);
            $table->string('class_type', 50)->nullable();
            // $table->string('grade', 11);
            $table->float('percentage', 4)->nullable();
            $table->float('percentile', 4)->nullable();
            // $table->string('school', 50)->comment('Include School, Collage, University');
            $table->enum('status', [
                '0',
                '1',
                '2',
            ])->comment('0=Block, 1=Active, 2=Draft');
            $table->enum('type', [
                'Study',
                'Activity',
                'Graduation',
            ]);
            $table->enum('medium', [
                'English',
                'Gujarati',
                'Other',
            ]);
            $table->integer('rank');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
}
