<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crea una categoría "producto" para la prueba de productos
        $this->category = Category::factory()->create(['category_name' => 'Producto']);
        // Crea una categoría "membresia" para la prueba de membresías
        $this->membershipCategory = Category::factory()->create(['category_name' => 'Membresia']);
    }

    public function test_get_active_products()
    {
        // Utiliza la ProductFactory para crear productos activos
        $product = Product::factory()->create(['active' => true, 'category_id' => $this->category->id]);

        $response = $this->get('/productos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'product_name',
                         'description',
                         'price',
                         'stock',
                         'discount',
                         'active',
                         'category_id',
                         'category_name',
                         'product_image_path',
                         'created_at',
                         'updated_at'
                     ]
                 ]);
    }

    public function test_get_all_products()
    {
        // Utiliza la ProductFactory para crear productos activos e inactivos
        Product::factory()->create(['active' => true, 'category_id' => $this->category->id]);
        Product::factory()->create(['active' => false, 'category_id' => $this->category->id]);

        $response = $this->get('/productos/all');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'product_name',
                         'description',
                         'price',
                         'stock',
                         'discount',
                         'active',
                         'category_id',
                         'category_name',
                         'product_image_path',
                         'created_at',
                         'updated_at'
                     ]
                 ]);
    }

    public function test_create_product()
    {
        Sanctum::actingAs(User::factory()->create(), ['role.admin']);

        $response = $this->post('/productos', [
            'product_name' => 'Nuevo Producto',
            'description' => 'Descripción del producto',
            'price' => 100.00,
            'stock' => 10,
            'discount' => 5,
            'active' => true,
            'category_id' => $this->category->id, // Categoría "producto"
            'product_image_path' => 'path/to/image.jpg'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['product_name' => 'Nuevo Producto']);
    }

    public function test_update_product()
    {
        Sanctum::actingAs(User::factory()->create(), ['role.admin']);
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->put('/productos/' . $product->id, [
            'product_name' => 'Producto Actualizado',
            'description' => 'Descripción actualizada',
            'price' => 150.00,
            'stock' => 20,
            'discount' => 10,
            'active' => false,
            'category_id' => $this->category->id,
            'product_image_path' => 'path/to/new_image.jpg'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['product_name' => 'Producto Actualizado']);
    }

    public function test_delete_product()
    {
        Sanctum::actingAs(User::factory()->create(), ['role.admin']);
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->delete('/productos/' . $product->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_toggle_active_product()
    {
        Sanctum::actingAs(User::factory()->create(), ['role.admin']);
        $product = Product::factory()->create(['active' => true, 'category_id' => $this->category->id]);

        $response = $this->put('/productos/' . $product->id . '/toggle-active');

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'active' => false]);
    }
}