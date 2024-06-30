<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\MembershipDetail;
use App\Models\Category;

class MembershipController extends Controller
{
    // GET - /membresias
    public function index()
    {
        $memberships = Product::where('active', true)
            ->whereHas('membershipDetail')
            ->join('membership_details', 'products.id', '=', 'membership_details.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.product_name', 'products.description', 'products.price', 'products.discount', 'products.active', 'products.category_id', 'categories.category_name', 'products.product_image_path', 'membership_details.duration_days', 'membership_details.size', 'products.created_at', 'products.updated_at')
            ->get();

        return response()->json($memberships);
    }

    // GET - /membresias/all
    public function indexAll()
    {
        $memberships = Product::whereHas('membershipDetail')
            ->join('membership_details', 'products.id', '=', 'membership_details.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.product_name', 'products.description', 'products.price', 'products.discount', 'products.active', 'products.category_id', 'categories.category_name', 'products.product_image_path', 'membership_details.duration_days', 'membership_details.size', 'products.created_at', 'products.updated_at')
            ->get();

        return response()->json($memberships);
    }

    // POST - /membresias
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:30',
            'description' => 'required|string|max:200',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric|max:100',
            'active' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'product_image_path' => 'nullable|file|image|max:2048',
            'duration_days' => 'required|integer',
            'size' => 'required|integer',
        ]);

        $path = $request->file('product_image_path') ? $request->file('product_image_path')->store('products') : null;

        $product = Product::create([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'discount' => $request->discount,
            'active' => $request->active,
            'category_id' => $request->category_id,
            'product_image_path' => $path,
        ]);

        MembershipDetail::create([
            'product_id' => $product->id,
            'duration_days' => $request->duration_days,
            'size' => $request->size,
        ]);

        return response()->json($product, 201);
    }

    // PUT - /membresias/{id}
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $membershipDetail = MembershipDetail::where('product_id', $id)->firstOrFail();

        $request->validate([
            'product_name' => 'required|string|max:30',
            'description' => 'required|string|max:200',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric|max:100',
            'active' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'product_image_path' => 'nullable|file|image|max:2048',
            'duration_days' => 'required|integer',
            'size' => 'required|integer',
        ]);

        $path = $product->product_image_path;
        if ($request->file('product_image_path')) {
            $path = $request->file('product_image_path')->store('products');
        }

        $product->update([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'discount' => $request->discount,
            'active' => $request->active,
            'category_id' => $request->category_id,
            'product_image_path' => $path,
        ]);

        $membershipDetail->update([
            'duration_days' => $request->duration_days,
            'size' => $request->size,
        ]);

        return response()->json($product);
    }

    // DELETE - /membresias/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $membershipDetail = MembershipDetail::where('product_id', $id)->firstOrFail();
        
        $membershipDetail->delete();
        $product->delete();

        return response()->json(null, 204);
    }

    // PUT - /membresias/{id}/toggle-active
    public function toggleActive($id)
    {
        $product = Product::findOrFail($id);
        $product->active = !$product->active;
        $product->save();

        return response()->json($product);
    }
}
