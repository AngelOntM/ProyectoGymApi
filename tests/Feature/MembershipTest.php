<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\MembershipDetail;
use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_active_memberships()
    {
        $product = Product::factory()->create(['active' => true, 'category_id' => $this->category->id]);
        MembershipDetail::factory()->create(['product_id' => $product->id]);

        $response = $this->get('/membresias');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'product_name',
                         'description',
                         'price',
                         'discount',
                         'active',
                         'category_id',
                         'category_name',
                         'product_image_path',
                         'created_at',
                         'updated_at',
                         'duration_days',
                         'size'
                     ]
                 ]);
    }

    public function test_get_all_memberships()
    {
        $product1 = Product::factory()->create(['active' => true, 'category_id' => $this->category->id]);
        $product2 = Product::factory()->create(['active' => false, 'category_id' => $this->category->id]);
        MembershipDetail::factory()->create(['product_id' => $product1->id]);
        MembershipDetail::factory()->create(['product_id' => $product2->id]);

        $response = $this->get('/membresias/all');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'product_name',
                         'description',
                         'price',
                         'discount',
                         'active',
                         'category_id',
                         'category_name',
                         'product_image_path',
                         'created_at',
                         'updated_at',
                         'duration_days',
                         'size'
                     ]
                 ]);
    }

    public function test_create_membership()
    {
        Sanctum::actingAs(User::factory()->create(), ['role.admin']);

        $response = $this->post('/membresias', [
            'product_name' => 'Nueva Membresía',
            'description' => 'Descripción de la membresía',
            'price' => 300.00,
            'discount' => 5,
            'active' => true,
            'category_id' => $this->category->id,
            'product_image_path' => 'path/to/image.jpg',
            'duration_days' => 30,
            'size' => 1
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['product_name' => 'Nueva Membresía']);
        $this->assertDatabaseHas('membership_details', ['duration_days' => 30, 'size' => 1]);
    }

    public function test_update_membership()
    {
        Sanctum::actingAs(User::factory()->create(), ['role.admin']);
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        $membershipDetail = MembershipDetail::factory()->create(['product_id' => $product->id]);

        $response = $this->put('/membresias/' . $product->id, [
            'product_name' => 'Membresía Actualizada',
            'description' => 'Descripción actualizada',
            'price' => 350.00,
            'discount' => 10,
            'active' => false,
            'category_id' => $this->category->id,
            'product_image_path' => 'path/to/new_image.jpg',
            'duration_days' => 60,
            'size' => 2
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['product_name' => 'Membresía Actualizada']);
        $this->assertDatabaseHas('membership_details', ['duration_days' => 60, 'size' => 2]);
    }

    public function test_delete_membership()
    {
        $membership = MembershipDetail::factory()->create();

        $response = $this->delete('/api/membresias/' . $membership->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('membership_details', ['id' => $membership->id]);
    }

    public function test_toggle_membership_active()
    {
        $membership = MembershipDetail::factory()->create(['active' => true]);

        $response = $this->put('/api/membresias/' . $membership->id . '/toggle-active');

        $response->assertStatus(200)
                 ->assertJsonFragment(['active' => false]);

        $membership->refresh();
        $this->assertFalse($membership->active);
    }
}
