<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolsTableSeeder extends Seeder
{
    public function run()
    {
        Rol::insert([
            ['rol_name' => 'Admin'],
            ['rol_name' => 'Empleado'],
            ['rol_name' => 'Cliente'],
        ]);
    }
}

