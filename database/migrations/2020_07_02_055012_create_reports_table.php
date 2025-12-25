<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuidMorphs('reportable');
            $table->string('value')->nullable();
            $table->uuid('member_id')->nullable();
            $table->uuid('report_user_id')->nullable();
            $table->string('report_user_notes')->nullable();
            $table->uuid('action_user_id')->nullable();
            $table->string('action_user_notes')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', [
                'open',
                'pending',
                'working',
                'solve',
                'reject',
            ])->default('open');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
}
