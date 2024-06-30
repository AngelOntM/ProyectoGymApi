<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MembershipDetail;
use App\Models\Product;

class MembershipDetailsTableSeeder extends Seeder
{
    public function run()
    {
        $products = Product::whereIn('product_name', ['Individual', 'Familiar', 'Estudiante'])->get();

        foreach ($products as $product) {
            if ($product->product_name == 'Individual') {
                MembershipDetail::create([
                    'product_id' => $product->id,
                    'duration_days' => 30,
                    'size' => 1,
                ]);
            } elseif ($product->product_name == 'Familiar') {
                MembershipDetail::create([
                    'product_id' => $product->id,
                    'duration_days' => 30,
                    'size' => 4,
                ]);
            } elseif ($product->product_name == 'Estudiante') {
                MembershipDetail::create([
                    'product_id' => $product->id,
                    'duration_days' => 30,
                    'size' => 1,
                ]);
            }
        }
    }
}
