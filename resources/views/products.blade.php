@extends('layouts.app')
@section('title', 'Products')
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
        <h1 class="text-2xl font-bold text-gray-900">All Products</h1>
        <p class="text-sm text-gray-500">{{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }} found</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Filters --}}
        <aside class="lg:w-56 shrink-0">
            <form method="GET" action="{{ route('products.index') }}" id="filter-form">
                <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-6">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Category</h2>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }}
                                    class="text-gray-900" onchange="this.form.submit()">
                                <span class="text-gray-700">All</span>
                            </label>
                            @foreach($categories as $cat)
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="category" value="{{ $cat->id }}"
                                    {{ request('category') == $cat->id ? 'checked' : '' }}
                                    class="text-gray-900" onchange="this.form.submit()">
                                <span class="text-gray-700">{{ $cat->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Price Range</h2>
                        <div class="space-y-2">
                            <div>
                                <label class="text-xs text-gray-500 block mb-1">Min Price</label>
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="0"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-1">Max Price</label>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Any"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                            </div>
                            <button type="submit"
                                class="w-full mt-1 bg-gray-900 text-white text-sm rounded-lg px-3 py-2 hover:bg-gray-700 transition">
                                Apply
                            </button>
                        </div>
                    </div>

                    @if(request()->hasAny(['category', 'min_price', 'max_price']))
                    <a href="{{ route('products.index') }}"
                       class="block text-center text-xs text-red-500 hover:text-red-700 transition">
                        Clear filters
                    </a>
                    @endif
                </div>
            </form>
        </aside>

        {{-- Product Grid --}}
        <div class="flex-1">
            @if($products->isEmpty())
                <div class="text-center py-20 bg-white rounded-xl border border-gray-100">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No products found.</p>
                    <a href="{{ route('products.index') }}" class="mt-3 inline-block text-sm text-gray-700 underline">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}"
                       class="bg-white rounded-xl border border-gray-100 hover:shadow-lg transition group overflow-hidden flex flex-col">
                        <div class="bg-gray-100 h-44 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <p class="text-xs text-gray-400 mb-1">{{ $product->category?->name }}</p>
                            <h3 class="font-semibold text-gray-900 text-sm group-hover:text-gray-600 transition line-clamp-2 flex-1">
                                {{ $product->name }}
                            </h3>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-base font-bold text-gray-900">${{ number_format($product->base_price, 2) }}</span>
                                @php $stock = $product->defaultVariant?->available_stock ?? 0; @endphp
                                @if($stock > 0)
                                    <span class="text-xs text-green-600 font-medium">In stock</span>
                                @else
                                    <span class="text-xs text-red-500 font-medium">Out of stock</span>
                                @endif
                            </div>
                            @php $activeDiscount = $product->discounts->first(); @endphp
                            @if($activeDiscount)
                                <span class="mt-2 inline-block text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium self-start">
                                    {{ $activeDiscount->type === 'percentage' ? $activeDiscount->value.'% OFF' : '$'.$activeDiscount->value.' OFF' }}
                                </span>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection