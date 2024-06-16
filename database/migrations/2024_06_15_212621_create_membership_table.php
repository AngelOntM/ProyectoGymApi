<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembershipTable extends Migration
{
    public function up()
    {
        Schema::create('membership', function (Blueprint $table) {
            $table->id();
            $table->string('membership_type', 20);
            $table->decimal('price', 10, 2);
            $table->integer('duration_days');
            $table->integer('size');
            $table->boolean('active');
            $table->string('benefits', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('membership');
    }
}
