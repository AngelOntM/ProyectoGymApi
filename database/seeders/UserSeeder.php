<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Asegúrate de que el rol existe antes de crear el usuario
        $adminRoleId = DB::table('rols')->where('rol_name', 'Admin')->value('id');
        
        if (!$adminRoleId) {
            $adminRoleId = DB::table('rols')->insertGetId([
                'rol_name' => 'Admin',
            ]);
        }

        User::insert([
            [
            'name' => 'Angel Jahaziel Ontiveros Mendez',
            'email' => 'ajom0507@hotmail.com',
            'password' => Hash::make('asdfasdf'), 
            'phone_number' => '1234567890',
            'address' => 'Villas la merced',
            'date_of_birth' => '2002-07-05',
            'rol_id' => $adminRoleId,
            ],
            [
            'name' => 'Gerita Plata',
            'email' => 'gerardogplata@gmail.com',
            'password' => Hash::make('gatitos'), 
            'phone_number' => '',
            'address' => 'Latinoamericano',
            'date_of_birth' => '1994-06-11',
            'rol_id' => $adminRoleId,
            ],
            [
            'name' => 'yan odz',
            'email' => 'yanordazuwu@gmail.com',
            'password' => Hash::make('gatitos'), 
            'phone_number' => '',
            'address' => 'UTT',
            'date_of_birth' => '2001-07-08',
            'rol_id' => $adminRoleId,
            ],
            [
            'name' => 'jose olivo',
            'email' => 'joseignacioolivorios@gmail.com',
            'password' => Hash::make('joseolivo'), 
            'phone_number' => '8713325689',
            'address' => 'calle de la uva',
            'date_of_birth' => '2002-06-11',
            'rol_id' => $adminRoleId,
            ],
    ]);
    }
}