<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolsTableSeeder::class,
            CategoriesTableSeeder::class,
            UsersTableSeeder::class,
            ProductsTableSeeder::class,
            MembershipDetailsTableSeeder::class,
            PaymentMethodsTableSeeder::class,
        ]);
    }
}
