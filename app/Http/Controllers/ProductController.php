<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['offers' => fn($q) => $q->where('in_stock', true)->orderBy('price')]);

        // Search
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Platform filter
        if ($platform = $request->get('platform')) {
            $query->where('platform', $platform);
        }

        // Category filter
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('name')->paginate(24)->withQueryString();

        // Aggregations
        $stats = [
            'products' => Product::count(),
            'offers' => \App\Models\Offer::count(),
            'stores' => Store::count(),
        ];

        $platforms = Product::whereNotNull('platform')->distinct()->pluck('platform');
        $categories = Product::whereNotNull('category')->distinct()->pluck('category');

        return view('products.index', compact('products', 'stats', 'platforms', 'categories'));
    }

    public function show(string $id): View
    {
        $product = Product::with(['offers.store'])->findOrFail($id);

        // Sort offers by price
        $offers = $product->offers->sortBy('price')->values();
        $bestOffer = $offers->where('in_stock', true)->first();

        return view('products.show', compact('product', 'offers', 'bestOffer'));
    }

    public function home(): View
    {
        $stats = [
            'products' => Product::count(),
            'offers' => \App\Models\Offer::count(),
            'stores' => Store::count(),
        ];

        $featured = Product::with(['offers' => fn($q) => $q->where('in_stock', true)->orderBy('price')])
            ->orderBy('updated_at', 'desc')
            ->limit(12)
            ->get();

        $platforms = Product::whereNotNull('platform')->distinct()->pluck('platform');

        return view('home', compact('stats', 'featured', 'platforms'));
    }
}
