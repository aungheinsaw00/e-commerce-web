@extends('layouts.app')

@section('title', 'Products & Inventory')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-10 gap-4">
        <h1 class="text-4xl font-bold tracking-tight text-gray-900">Products & Inventory</h1>
        <div class="flex space-x-4 items-center">
            <p class="text-sm font-medium text-gray-500 mr-4">{{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}</p>
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                Manage Categories
            </a>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-sm transition">
                Add Product
            </a>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Filters --}}
        <aside class="lg:w-64 shrink-0">
            <form method="GET" action="{{ route('admin.products.index') }}" id="filter-form">
                <div class="bg-white rounded-2xl border border-gray-100/50 shadow-sm p-6 space-y-8 sticky top-24">
                    <div>
                        <h2 class="text-sm font-bold tracking-wider text-gray-900 uppercase mb-4">Category</h2>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 text-sm cursor-pointer group">
                                <input type="radio" name="category_id" value="" {{ !request('category_id') ? 'checked' : '' }}
                                    class="w-4 h-4 text-black border-gray-300 focus:ring-black transition" onchange="this.form.submit()">
                                <span class="text-gray-600 group-hover:text-black transition-colors font-medium">All Categories</span>
                            </label>
                            @foreach($categories as $cat)
                            <label class="flex items-center gap-3 text-sm cursor-pointer group">
                                <input type="radio" name="category_id" value="{{ $cat->id }}"
                                    {{ request('category_id') == $cat->id ? 'checked' : '' }}
                                    class="w-4 h-4 text-black border-gray-300 focus:ring-black transition" onchange="this.form.submit()">
                                <span class="text-gray-600 group-hover:text-black transition-colors font-medium">{{ $cat->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    @if(request()->has('category_id') && request('category_id') != '')
                    <div class="pt-4 text-center border-t border-gray-100">
                        <a href="{{ route('admin.products.index') }}"
                           class="inline-block text-xs font-medium text-red-500 hover:text-red-700 transition-colors border-b border-red-200 hover:border-red-700 mt-4">
                            Clear filters
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </aside>

        {{-- Product Grid --}}
        <div class="flex-1 min-w-0">
            @if($products->isEmpty())
                <div class="text-center py-20 bg-white rounded-xl border border-gray-100">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No products found.</p>
                    <a href="{{ route('admin.products.index') }}" class="mt-3 inline-block text-sm text-gray-700 underline">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($products as $product)
                    @php
                        $totalStock = 0;
                        $totalReserved = 0;
                        foreach($product->variants as $variant) {
                            if($variant->inventory) {
                                $totalStock += $variant->inventory->stock_quantity;
                                $totalReserved += $variant->inventory->reserved_quantity;
                            }
                        }
                        $availableStock = max(0, $totalStock - $totalReserved);
                        $stockPercent = $totalStock > 0 ? min(100, ($availableStock / $totalStock) * 100) : 0;
                        
                        if ($availableStock > 10) {
                            $stockColor = 'bg-green-500';
                            $stockText = 'text-green-700';
                            $stockBg = 'bg-green-50';
                            $stockBorder = 'border-green-200';
                        } elseif ($availableStock > 0) {
                            $stockColor = 'bg-red-500'; // Low stock
                            $stockText = 'text-red-700';
                            $stockBg = 'bg-red-50';
                            $stockBorder = 'border-red-200';
                        } else {
                            $stockColor = 'bg-gray-400';
                            $stockText = 'text-gray-700';
                            $stockBg = 'bg-gray-100';
                            $stockBorder = 'border-gray-200';
                        }
                    @endphp
                    <a href="{{ route('admin.products.edit', $product) }}" class="block bg-white rounded-3xl border border-gray-100/50 shadow-sm hover:shadow-xl transition-all duration-300 group overflow-hidden flex flex-col relative">
                        <div class="bg-[#f5f5f7] h-48 flex items-center justify-center p-6 relative">
                            <img src="{{ $product->image_path ? asset('storage/'.$product->image_path) : '/placeholder.png' }}" alt="" class="h-full w-full object-cover rounded-t-3xl mix-blend-multiply group-hover:scale-105 transition-transform duration-500">
                            
                            @if(!$product->is_active)
                                <span class="absolute top-4 left-4 bg-red-100 text-red-800 text-xs px-2.5 py-1 rounded-full font-bold tracking-wide border border-red-200">
                                    INACTIVE
                                </span>
                            @endif
                        </div>
                        <div class="p-5 flex-1 flex flex-col bg-white">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-xs font-semibold tracking-wider text-gray-400 uppercase">{{ $product->category?->name }}</p>
                                <span class="text-lg font-bold tracking-tight text-gray-900">${{ number_format($product->base_price, 2) }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-base leading-snug group-hover:text-blue-600 transition-colors line-clamp-2 mb-4">
                                {{ $product->name }}
                            </h3>
                            
                            <div class="mt-auto space-y-3">
                                <!-- Inventory Details -->
                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                    <div class="text-xs space-y-1 mb-2 font-medium">
                                        <div class="text-green-600">Available: {{ $availableStock }}</div>
                                        <div class="text-yellow-600">Reserved: {{ $totalReserved }}</div>
                                        <div class="text-gray-900">Total: {{ $totalStock }}</div>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden flex">
                                        <div class="{{ $stockColor }} h-1.5 rounded-full" style="width: {{ $stockPercent }}%"></div>
                                        @if($totalReserved > 0 && $totalStock > 0)
                                            <div class="bg-yellow-400 h-1.5" style="width: {{ ($totalReserved / $totalStock) * 100 }}%"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    @if($products->hasPages())
                        <div class="mb-4 text-sm text-gray-500 font-medium text-center">
                            Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                        </div>
                    @endif
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection