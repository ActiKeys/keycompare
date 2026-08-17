<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('featuredImage');
        if ($q = $request->get('q')) {
            $query->where('name', 'like', "%{$q}%");
        }
        $products = $query->orderBy('id', 'desc')->paginate(30);
        return view('admin.products.index', compact('products'));
    }

    public function edit(Product $product)
    {
        $product->load('media');
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'nullable|string|max:64',
            'category' => 'nullable|string|max:128',
            'description' => 'nullable|string',
            'image_link' => 'nullable|url|max:2048',
        ]);
        $product->update($data);
        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated');
    }
}
