<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        Category::insert([
            ['category_name' => 'Producto'],
            ['category_name' => 'Membresia'],
        ]);
    }
}
