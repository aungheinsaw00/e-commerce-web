@extends('layouts.app')
@section('title', 'Home')
@section('content')

{{-- Hero --}}
<section class="bg-gray-900 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">
            Best Car Parts for Your Vehicle
        </h1>
        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
            Quality parts. Trusted brands. Fast shipping. Find everything your vehicle needs.
        </p>
        <a href="{{ route('products.index') }}"
           class="inline-block bg-white text-gray-900 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition">
            Shop Now
        </a>
    </div>
</section>

{{-- Categories --}}
@if($categories->isNotEmpty())
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Browse by Category</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->id]) }}"
               class="flex flex-col items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 hover:shadow-md transition-all duration-300 border border-gray-100 group">
                <div class="w-12 h-12 bg-gray-200 group-hover:bg-gray-900 group-hover:scale-110 rounded-full flex items-center justify-center mb-3 transition-all duration-300">
                    <x-category-icon :name="$category->name" class="w-6 h-6 text-gray-600 group-hover:text-white transition-colors duration-300" />
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">{{ $category->name }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Featured / Discounted Products --}}
@if($discountedProducts->isNotEmpty())
<section class="py-14 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Featured Deals</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($discountedProducts as $product)
            <a href="{{ route('products.show', $product->slug) }}"
               class="bg-white rounded-xl border border-gray-100 hover:shadow-lg transition group overflow-hidden">
                <div class="bg-gray-100 h-44 flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">{{ $product->category?->name }}</p>
                    <h3 class="font-semibold text-gray-900 group-hover:text-gray-700 transition text-sm line-clamp-2">
                        {{ $product->name }}
                    </h3>
                    <p class="mt-2 text-lg font-bold text-gray-900">${{ number_format($product->base_price, 2) }}</p>
                    @php $activeDiscount = $product->discounts->first(); @endphp
                    @if($activeDiscount)
                        <span class="inline-block mt-1 text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">
                            {{ $activeDiscount->type === 'percentage' ? $activeDiscount->value.'% OFF' : '$'.$activeDiscount->value.' OFF' }}
                        </span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-8 text-center">
            <a href="{{ route('products.index') }}" class="inline-block border border-gray-900 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-900 hover:text-white transition font-medium text-sm">
                View All Products
            </a>
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Ready to get started?</h2>
        <p class="text-gray-300 mb-8">Create an account to track orders, manage your cart, and access exclusive deals.</p>
        @guest
            <div class="flex gap-4 justify-center">
                <a href="{{ route('register') }}" class="bg-white text-gray-900 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition">Create Account</a>
                <a href="{{ route('login') }}" class="border border-white text-white font-semibold px-6 py-3 rounded-lg hover:bg-white/10 transition">Sign In</a>
            </div>
        @else
            <a href="{{ route('products.index') }}" class="bg-white text-gray-900 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition">Browse Products</a>
        @endguest
    </div>
</section>

@endsection