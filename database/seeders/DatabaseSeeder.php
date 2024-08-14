<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Database\Factories\MembershipProductFactory;
use App\Models\User;

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

        //Product::factory()->count(10)->create();

        //User::factory()->count(5)->create();
    }
}
