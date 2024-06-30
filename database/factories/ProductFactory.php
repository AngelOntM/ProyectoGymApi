<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'product_name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
            'discount' => $this->faker->numberBetween(0, 50),
            'active' => $this->faker->boolean(80), // 80% de probabilidad de estar activo
            'category_id' => Category::where('category_name', 'Producto')->first()->id,
            'product_image_path' => $this->faker->imageUrl(),
        ];
    }
}