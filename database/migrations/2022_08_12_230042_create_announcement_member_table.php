<?php

use App\Models\Announcement;
use App\Models\Member;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('announcement_member', function (Blueprint $table) {
            $table->foreignIdFor(Announcement::class);
            $table->foreignIdFor(Member::class);
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcement_member');
    }
};
