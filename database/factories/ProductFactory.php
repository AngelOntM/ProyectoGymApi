<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\MembershipDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        // Genera un número aleatorio entre 1 y 2 (representando los category_id posibles)
        $categoryId = $this->faker->randomElement([1, 2]);

        // Define los atributos comunes del producto
        $product = [
            'product_name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
            'discount' => $this->faker->numberBetween(0, 50),
            'active' => $this->faker->boolean(60),
            'category_id' => $categoryId,
            'product_image_path' => $this->faker->imageUrl(),
        ];

        // Si el category_id es 2, crea automáticamente MembershipDetail
        if ($categoryId === 2) {
            $productInstance = Product::create($product);

            MembershipDetail::create([
                'product_id' => $productInstance->id,
                'duration_days' => $this->faker->numberBetween(1, 365),
                'size' => $this->faker->numberBetween(1, 5),
            ]);
        }

        return $product;
    }

    /**
     * Define un estado para crear productos con un category_id específico.
     *
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withCategoryId(int $categoryId)
    {
        return $this->state(function (array $attributes) use ($categoryId) {
            return [
                'category_id' => $categoryId,
            ];
        });
    }
}
