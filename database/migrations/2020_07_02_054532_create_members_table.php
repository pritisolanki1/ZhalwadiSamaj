<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('father_id')->nullable();
            $table->uuid('mother_id')->nullable();
            $table->uuid('head_of_the_family_id')->nullable();
            $table->uuid('relation_id')->nullable();
            $table->uuid('native_place_id')->nullable();
            $table->json('name')->comment('multi language name in json_array');
            $table->string('name_en', 100)->nullable();
            $table->enum('gender', [
                'Male',
                'Female',
            ]);
            $table->date('birth_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('device_token')->nullable();
            $table->string('device_serial')->nullable();
            $table->rememberToken();
            $table->enum('blood_group', [
                'A+',
                'A-',
                'B+',
                'B-',
                'AB+',
                'AB-',
                'O+',
                'O-',
            ])->nullable();
            $table->json('address')->nullable();
            $table->json('occupation')->nullable();
            $table->json('qualification')->nullable();
            $table->text('avatar')->nullable();
            $table->json('slider')->nullable();
            $table->enum('status', [
                '0',
                '1',
                '2',
            ])->comment('0=Block, 1=Active, 2=Draft')->default('2');
            $table->string('reason')->nullable();
            $table->enum('notification_status', [
                '0',
                '1',
            ])->comment('0=Block, 1=Active')->default('1');
            $table->enum('is_private', [
                '0',
                '1',
            ])->default('0');
            $table->enum('relationShip_status', [
                'Single',
                'Engaged',
                'Married',
                'Divorced',
                'Widow',
                'Widower',
            ]);
            $table->json('profession')->nullable();
            $table->json('profession_type')->nullable();
            $table->json('work_address')->nullable();
            $table->json('mosal')->nullable();
            $table->boolean('is_login_auth')->default('0');
            $table->json('mother_name')->comment('multi lang. name in json array')->nullable();
            $table->json('father_name')->comment('multi lang. name in json array')->nullable();
            $table->string('education')->nullable();
            $table->uuid('zone_id')->nullable();
            $table->uuid('kuldevi_id')->nullable();
            $table->string('pancard', 20)->nullable();
            $table->string('unique_number', 20)->nullable();
            $table->decimal('total_donation')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
}
