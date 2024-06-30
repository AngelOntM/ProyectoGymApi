<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\MembershipDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $category = Category::where('category_name', 'Membresia')->first();

        $product = [
            'product_name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 50, 500),
            'stock' => $this->faker->numberBetween(0, 50),
            'discount' => $this->faker->numberBetween(0, 30),
            'active' => $this->faker->boolean(80), // 80% de probabilidad de estar activo
            'category_id' => $category->id,
            'product_image_path' => $this->faker->imageUrl(),
        ];

        $productInstance = Product::create($product);

        MembershipDetail::create([
            'product_id' => $productInstance->id,
            'duration_days' => $this->faker->numberBetween(1, 365),
            'size' => $this->faker->numberBetween(1, 5),
        ]);

        return $product;
    }
}