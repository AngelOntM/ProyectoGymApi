<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 30);
            $table->string('description', 200);
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->decimal('discount', 3, 0)->default(0);
            $table->boolean('active');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('product_image_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
