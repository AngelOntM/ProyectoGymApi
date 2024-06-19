<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
