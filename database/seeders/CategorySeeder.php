<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Interior Accessories', 'slug' => 'interior-accessories'],
            ['name' => 'Cleaning Supplies', 'slug' => 'cleaning-supplies'],
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Safety Equipment', 'slug' => 'safety-equipment'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}