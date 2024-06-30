<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodsTableSeeder extends Seeder
{
    public function run()
    {
        PaymentMethod::insert([
            ['method_name' => 'Efectivo'],
            ['method_name' => 'Tarjeta de crédito'],
            ['method_name' => 'Tarjeta de débito'],
            ['method_name' => 'Pago en línea'],
            ['method_name' => 'Transferencia'],
        ]);
    }
}
