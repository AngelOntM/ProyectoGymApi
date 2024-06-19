<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rol::insert([
            ['rol_name' => 'Admin'],
            ['rol_name' => 'Empleado'],
            ['rol_name' => 'Cliente'],
        ]);
    }
}
