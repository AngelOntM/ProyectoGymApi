<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_name', 50);
            $table->timestamps();
        });

        // Insertar métodos de pago
        DB::table('payment_methods')->insert([
            ['method_name' => 'Efectivo'],
            ['method_name' => 'Tarjeta'],
            ['method_name' => 'Pago en línea'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}
