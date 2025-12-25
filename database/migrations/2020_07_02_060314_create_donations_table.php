<?php

use App\Models\Donation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('member_id')->nullable();
            $table->enum('donations_type', Donation::DONATION_TYPES);
            $table->decimal('amount');
            $table->date('date');
            $table->string('transition_id', 100)->nullable();
            $table->json('transition')->nullable();
            $table->enum('transition_status', Donation::TRANSITION_STATUSES);
            $table->enum('status', Donation::STATUSES);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
}
