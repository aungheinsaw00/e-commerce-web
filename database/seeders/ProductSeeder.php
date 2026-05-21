<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Car Air Freshener', 'description' => 'Lemon fragrance air freshener for cars', 'base_price' => 3.50, 'category' => 'interior-accessories', 'stock' => 100],
            ['name' => 'Seat Cushion', 'description' => 'Comfortable ergonomic seat cushion', 'base_price' => 15.00, 'category' => 'interior-accessories', 'stock' => 50],
            ['name' => 'Steering Wheel Cover', 'description' => 'Non-slip premium steering wheel cover', 'base_price' => 8.00, 'category' => 'interior-accessories', 'stock' => 75],
            ['name' => 'Car Trash Bin', 'description' => 'Compact leak-proof trash bin', 'base_price' => 5.00, 'category' => 'interior-accessories', 'stock' => 120],
            ['name' => 'Cup Holder', 'description' => 'Adjustable universal cup holder', 'base_price' => 4.00, 'category' => 'interior-accessories', 'stock' => 200],
            ['name' => 'Microfiber Cloth', 'description' => 'Premium car cleaning microfiber cloth', 'base_price' => 2.00, 'category' => 'cleaning-supplies', 'stock' => 500],
            ['name' => 'Car Wash Sponge', 'description' => 'Soft premium wash sponge', 'base_price' => 3.00, 'category' => 'cleaning-supplies', 'stock' => 300],
            ['name' => 'Cleaning Brush', 'description' => 'Multi-purpose detailing brush', 'base_price' => 4.50, 'category' => 'cleaning-supplies', 'stock' => 150],
            ['name' => 'Tire Brush', 'description' => 'Heavy-duty tire cleaning brush', 'base_price' => 6.00, 'category' => 'cleaning-supplies', 'stock' => 100],
            ['name' => 'Cleaning Spray Bottle', 'description' => 'Reusable 500ml spray bottle', 'base_price' => 3.50, 'category' => 'cleaning-supplies', 'stock' => 250],
            ['name' => 'Phone Holder', 'description' => 'Adjustable magnetic phone mount', 'base_price' => 10.00, 'category' => 'electronics', 'stock' => 80],
            ['name' => 'USB Car Charger', 'description' => 'Dual port fast charger', 'base_price' => 8.00, 'category' => 'electronics', 'stock' => 150],
            ['name' => 'Charging Cable', 'description' => 'USB-C fast charging cable', 'base_price' => 5.00, 'category' => 'electronics', 'stock' => 400],
            ['name' => 'Dashboard Mat', 'description' => 'Non-slip silicone dashboard mat', 'base_price' => 7.00, 'category' => 'electronics', 'stock' => 100],
            ['name' => 'Key Holder', 'description' => 'Stylish leather key holder', 'base_price' => 3.50, 'category' => 'electronics', 'stock' => 200],
            ['name' => 'Emergency Hammer', 'description' => 'Window breaker & seat belt cutter combo', 'base_price' => 7.50, 'category' => 'safety-equipment', 'stock' => 60],
            ['name' => 'Seat Belt Cutter', 'description' => 'Emergency safety cutter', 'base_price' => 6.00, 'category' => 'safety-equipment', 'stock' => 80],
            ['name' => 'Reflective Warning Triangle', 'description' => 'Foldable emergency triangle with case', 'base_price' => 12.00, 'category' => 'safety-equipment', 'stock' => 40],
            ['name' => 'Tire Pressure Gauge', 'description' => 'Digital accurate tire pressure gauge', 'base_price' => 5.50, 'category' => 'safety-equipment', 'stock' => 100],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category'])->first();
            if (!$category) continue;

            $slug = Str::slug($data['name']);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'],
                    'category_id' => $category->id,
                    'base_price' => $data['base_price'],
                    'images' => null,
                    'is_active' => true,
                ]
            );

            $sku = strtoupper(Str::slug($data['name'], '-'));
            $variant = ProductVariant::updateOrCreate(
                ['sku' => $sku],
                [
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'name' => 'Default',
                    'is_active' => true,
                ]
            );

            Inventory::updateOrCreate(
                ['variant_id' => $variant->id],
                [
                    'stock_quantity' => $data['stock'],
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 5,
                ]
            );
        }
    }
}