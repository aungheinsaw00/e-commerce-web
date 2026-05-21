<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with('category', 'variants.inventory')
            ->withTrashed()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string',
            'base_price'    => 'required|numeric|min:0',
            'category_id'   => 'required|exists:categories,id',
            'images'        => 'nullable|array',
            'sku'           => 'required|string|unique:product_variants,sku',
            'initial_stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($request->only('name', 'description', 'base_price', 'category_id', 'images'));

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => $validated['sku'],
            'name'       => 'Default',
        ]);

        Inventory::create([
            'variant_id'     => $variant->id,
            'stock_quantity' => $validated['initial_stock'],
        ]);

        Cache::forget('home.featured');
        Cache::forget('categories.active');

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $product->load('variants.inventory');
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'base_price'  => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'images'      => 'nullable|array',
            'is_active'   => 'boolean',
        ]);

        $product->update($request->only('name', 'description', 'base_price', 'category_id', 'images', 'is_active'));

        Cache::forget('home.featured');

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete(); // soft delete

        Cache::forget('home.featured');

        return redirect()->route('admin.products.index')->with('success', 'Product archived.');
    }
}
