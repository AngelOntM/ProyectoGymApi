<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // Ajusta la generación del password según tu lógica
            'phone_number' => substr($this->faker->phoneNumber, 0, 10), // Limita el teléfono a 20 caracteres
            'address' => substr($this->faker->address, 0, 60),
            'date_of_birth' => $this->faker->date(),
            'rol_id' => 3, // Por defecto, creará usuarios con rol_id igual a 2
            'face_image_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

