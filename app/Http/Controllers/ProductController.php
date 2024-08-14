<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // GET - /productos
    public function index()
    {
        try {
            $products = Product::where('active', true)
                ->where('products.category_id', 1)
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.id', 'products.product_name', 'products.description', 'products.price', 'products.stock', 'products.discount', 'products.active', 'products.category_id', 'categories.category_name', 'products.product_image_path', 'products.created_at', 'products.updated_at')
                ->orderBy('products.id', 'asc')
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener los productos'], 500);
        }
    }

    // GET - /productos/all
    public function indexAll()
    {
        try {
            $products = Product::where('products.category_id', 1)
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.id', 'products.product_name', 'products.description', 'products.price', 'products.stock', 'products.discount', 'products.active', 'products.category_id', 'categories.category_name', 'products.product_image_path', 'products.created_at', 'products.updated_at')
                ->orderBy('products.id', 'asc')
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener todos los productos'], 500);
        }
    }

    // POST - /productos
    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:30',
                'description' => 'required|string|max:200',
                'price' => 'required|numeric',
                'stock' => 'required|integer',
                'discount' => 'nullable|numeric|max:100',
                'active' => 'required|boolean',
                'product_image_path' => 'nullable|file|image|max:8192',
            ]);

            $product = Product::create([
                'product_name' => $request->product_name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'discount' => $request->discount,
                'active' => $request->active,
                'category_id' => 1,
                'product_image_path' => null,
            ]);

            if ($request->hasFile('product_image_path')) {
                $file = $request->file('product_image_path');
                $fileName = $product->id . '.' . $file->getClientOriginalExtension();
                $destinationPath = storage_path('app/public/products');

                // Mover el archivo manualmente
                $file->move($destinationPath, $fileName);

                $product->update(['product_image_path' => 'products/' . $fileName]);
            }

            return response()->json($product, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el producto'], 500);
        }
    }


    // PUT - /productos/{id}
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $request->validate([
                'product_name' => 'required|string|max:30',
                'description' => 'required|string|max:200',
                'price' => 'required|numeric',
                'stock' => 'required|integer',
                'discount' => 'nullable|numeric|max:100',
                'active' => 'required|boolean',
                'product_image_path' => 'nullable|file|image|max:8192',
            ]);

            if ($request->hasFile('product_image_path')) {
                // Eliminar la imagen anterior si existe
                if ($product->product_image_path) {
                    $oldImagePath = storage_path('app/public/' . $product->product_image_path);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $file = $request->file('product_image_path');
                $fileName = $product->id . '.' . $file->getClientOriginalExtension();
                $destinationPath = storage_path('app/public/products');

                // Mover el archivo manualmente
                $file->move($destinationPath, $fileName);

                $product->update(['product_image_path' => 'products/' . $fileName]);
            } else {
                $product->update($request->only(['product_name', 'description', 'price', 'stock', 'discount', 'active']));
            }

            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el producto'], 500);
        }
    }

    
    // DELETE - /productos/{id}
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Borrar la imagen del producto
            if ($product->product_image_path) {
                Storage::delete('public/' . $product->product_image_path);  // Agregar 'public/' antes de borrar
            }

            $product->delete();

            return response()->json(['message' => 'Producto eliminado'], 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el producto'], 500);
        }
    }

    // PUT - /productos/{id}/toggle-active
    public function toggleActive($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->active = !$product->active;
            $product->save();

            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cambiar el estado del producto'], 500);
        }
    }
}
