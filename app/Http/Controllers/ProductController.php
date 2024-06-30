<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // GET - /productos
    public function index()
    {
        $products = Product::where('active', true)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.product_name', 'products.description', 'products.price', 'products.stock', 'products.discount', 'products.active', 'products.category_id', 'categories.category_name', 'products.product_image_path', 'products.created_at', 'products.updated_at')
            ->get();

        return response()->json($products);
    }

    // GET - /productos/all
    public function indexAll()
    {
        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.product_name', 'products.description', 'products.price', 'products.stock', 'products.discount', 'products.active', 'products.category_id', 'categories.category_name', 'products.product_image_path', 'products.created_at', 'products.updated_at')
            ->get();

        return response()->json($products);
    }

    // POST - /productos
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:30',
            'description' => 'required|string|max:200',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'discount' => 'nullable|numeric|max:100',
            'active' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'product_image_path' => 'nullable|file|image|max:2048',
        ]);

        $path = $request->file('product_image_path') ? $request->file('product_image_path')->store('products') : null;

        $product = Product::create([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'discount' => $request->discount,
            'active' => $request->active,
            'category_id' => $request->category_id,
            'product_image_path' => $path,
        ]);

        return response()->json($product, 201);
    }

    // PUT - /productos/{id}
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name' => 'required|string|max:30',
            'description' => 'required|string|max:200',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'discount' => 'nullable|numeric|max:100',
            'active' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'product_image_path' => 'nullable|file|image|max:2048',
        ]);

        $path = $product->product_image_path;
        if ($request->file('product_image_path')) {
            $path = $request->file('product_image_path')->store('products');
        }

        $product->update([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'discount' => $request->discount,
            'active' => $request->active,
            'category_id' => $request->category_id,
            'product_image_path' => $path,
        ]);

        return response()->json($product);
    }

    // DELETE - /productos/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }

    // PUT - /productos/{id}/toggle-active
    public function toggleActive($id)
    {
        $product = Product::findOrFail($id);
        $product->active = !$product->active;
        $product->save();

        return response()->json($product);
    }
}
