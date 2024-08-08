<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::insert([
            [
                'name' => 'Angel Jahaziel Ontiveros Mendez',
                'email' => 'ajom0507@hotmail.com',
                'password' => Hash::make('asdfasdf'),
                'phone_number' => '1234567890',
                'address' => 'Villas la merced',
                'date_of_birth' => '2002-07-05',
                'rol_id' => 1,
                'face_image_path' => null,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ],
            [
                'name' => 'Gerita Plata',
                'email' => 'gerardogplata@gmail.com',
                'password' => Hash::make('asdfasdf'),
                'phone_number' => '1234567890',
                'address' => 'Latinoamericano',
                'date_of_birth' => '1994-06-11',
                'rol_id' => 1,
                'face_image_path' => null,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ],
            [
                'name' => 'yan odz',
                'email' => 'yanordazuwu@gmail.com',
                'password' => Hash::make('gatitos'),
                'phone_number' => '1234567890',
                'address' => 'UTT',
                'date_of_birth' => '2001-07-08',
                'rol_id' => 1,
                'face_image_path' => null,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ],
            [
                'name' => 'jose olivo',
                'email' => 'joseignacioolivorios@gmail.com',
                'password' => Hash::make('joseolivo'),
                'phone_number' => '8713325689',
                'address' => 'calle de la uva',
                'date_of_birth' => '2002-06-11',
                'rol_id' => 1,
                'face_image_path' => null,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ],
            [
                'name' => 'Pepe pecas',
                'email' => 'pepitopecas@gmail.com',
                'password' => Hash::make('asdfasdf'),
                'phone_number' => '1234567890',
                'address' => 'utt',
                'date_of_birth' => '2000-04-03',
                'rol_id' => 2,
                'face_image_path' => null,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ],
            [
                'name' => 'Juanito',
                'email' => 'gerardo.sunset@gmail.com',
                'password' => Hash::make('asdfasdf'),
                'phone_number' => '1234567890',
                'address' => 'utt',
                'date_of_birth' => '2000-04-03',
                'rol_id' => 3,
                'face_image_path' => null,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ],
        ]);
    }
}
