<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolsTable extends Migration
{
    public function up()
    {
        Schema::create('rols', function (Blueprint $table) {
            $table->id();
            $table->string('rol_name', 30);
            $table->timestamps();
        });

        // Insertar roles
        DB::table('rols')->insert([
            ['rol_name' => 'Admin'],
            ['rol_name' => 'Empleado'],
            ['rol_name' => 'Cliente'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('rols');
    }
}