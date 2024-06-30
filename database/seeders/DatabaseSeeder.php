<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\MembershipDetail;
use App\Models\User;
use Database\Factories\UserFactory;

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

        Product::factory()->count(10)->create();

        Product::factory(MembershipProductFactory::class)->count(10)->create();

        User::factory()->count(5)->create();

    }
}
