<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        Product::insert([
            [
                'product_name' => 'Individual',
                'description' => 'Suscripción para una persona por 30 días',
                'price' => 300.00,
                'stock' => 100,
                'discount' => 0,
                'active' => 1,
                'category_id' => 2, // Assuming 'Membresia' has ID 2
                'product_image_path' => null,
            ],
            [
                'product_name' => 'Familiar',
                'description' => 'Suscripción para 4 personas por 30 días',
                'price' => 1000.00,
                'stock' => 50,
                'discount' => 0,
                'active' => 1,
                'category_id' => 2, // Assuming 'Membresia' has ID 2
                'product_image_path' => null,
            ],
            [
                'product_name' => 'Estudiante',
                'description' => 'Suscripción para un estudiante por 30 días',
                'price' => 250.00,
                'stock' => 100,
                'discount' => 0,
                'active' => 1,
                'category_id' => 2, // Assuming 'Membresia' has ID 2
                'product_image_path' => null,
            ],
        ]);
    }
}

