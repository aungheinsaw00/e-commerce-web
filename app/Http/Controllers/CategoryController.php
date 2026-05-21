<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::remember('categories.active', 3600, function () {
            return Category::where('is_active', true)->withCount('products')->get();
        });

        return view('categories', compact('categories'));
    }
}
