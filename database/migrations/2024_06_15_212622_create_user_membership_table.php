<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMembershipTable extends Migration
{
    public function up()
    {
        Schema::create('user_membership', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('membership_id')->constrained('membership');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('registration_group_id', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_membership');
    }
}
