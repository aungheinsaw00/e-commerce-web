<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Cache::remember('categories.active', 3600, function () {
            return Category::where('is_active', true)->get();
        });

        $query = Product::with('category', 'discounts', 'defaultVariant.inventory')
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }
        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', (float) $request->input('max_price'));
        }

        $products = $query->paginate(20)->withQueryString();

        return view('products', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load('category', 'discounts', 'variants.inventory');

        return view('product_detail', compact('product'));
    }
}