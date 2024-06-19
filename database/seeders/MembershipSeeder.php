<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Membership;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Membership::insert([
            [
            'membership_type' => 'Individual',
            'price' => '300.00',
            'duration_days' => '30',
            'size' => '1',
            'active' => '1',
            'benefits' => 'Suscipcion para una persona por 30 dias',
            ],
            [
            'membership_type' => 'Familiar',
            'price' => '1000.00',
            'duration_days' => '30',
            'size' => '4',
            'active' => '1',
            'benefits' => 'Suscipcion para 4 personas por 30 dias',
            ],
            [
            'membership_type' => 'Estudiante',
            'price' => '250.00',
            'duration_days' => '30',
            'size' => '1',
            'active' => '1',
            'benefits' => 'Suscipcion para un estudiante por 30 dias',
            ],
        ]);
    }
}
