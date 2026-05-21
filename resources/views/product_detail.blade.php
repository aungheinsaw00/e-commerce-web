@extends('layouts.app')
@section('title', $product->name)
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-gray-900 transition">Home</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-gray-900 transition">Products</a>
        <span>/</span>
        <span class="text-gray-700 font-medium">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

        {{-- Product Image --}}
        <div class="bg-gray-100 rounded-2xl flex items-center justify-center h-96">
            <svg class="w-32 h-32 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>

        {{-- Product Info --}}
        <div class="flex flex-col">
            @if($product->category)
                <span class="text-sm text-gray-500 mb-2">{{ $product->category->name }}</span>
            @endif

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

            {{-- Price & Discount --}}
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl font-bold text-gray-900">${{ number_format($product->base_price, 2) }}</span>
                @php $activeDiscount = $product->discounts->first(); @endphp
                @if($activeDiscount)
                    <span class="bg-red-100 text-red-600 text-sm px-3 py-1 rounded-full font-semibold">
                        {{ $activeDiscount->type === 'percentage' ? $activeDiscount->value.'% OFF' : '$'.$activeDiscount->value.' OFF' }}
                    </span>
                @endif
            </div>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $product->description }}</p>

            {{-- Add to Cart Form --}}
            @if($product->variants->isNotEmpty())
                @auth
                <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Variant select --}}
                    @if($product->variants->count() > 1)
                    <div>
                        <label for="variant_id" class="block text-sm font-medium text-gray-700 mb-1">Variant</label>
                        <select name="variant_id" id="variant_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                            @foreach($product->variants as $variant)
                                @if($variant->available_stock > 0)
                                <option value="{{ $variant->id }}">
                                    {{ $variant->name ?? 'Default' }}
                                    @if($variant->price_override) — ${{ number_format($variant->price_override, 2) }} @endif
                                    ({{ $variant->available_stock }} in stock)
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="variant_id" value="{{ $product->variants->first()->id }}">
                        @php $defaultVariant = $product->variants->first(); @endphp
                        <p class="text-sm text-gray-500">
                            Stock:
                            @if($defaultVariant->available_stock > 0)
                                <span class="text-green-600 font-medium">{{ $defaultVariant->available_stock }} available</span>
                            @else
                                <span class="text-red-500 font-medium">Out of stock</span>
                            @endif
                        </p>
                    @endif

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1"
                            class="w-28 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                    </div>

                    @php
                        $totalStock = $product->variants->sum('available_stock');
                    @endphp
                    <button type="submit"
                        @if($totalStock === 0) disabled @endif
                        class="w-full bg-gray-900 text-white font-semibold py-3 rounded-xl hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        @if($totalStock === 0) Out of Stock @else Add to Cart @endif
                    </button>
                </form>
                @else
                <div class="space-y-3">
                    <a href="{{ route('login') }}"
                       class="block text-center w-full bg-gray-900 text-white font-semibold py-3 rounded-xl hover:bg-gray-700 transition">
                        Log in to Add to Cart
                    </a>
                    <p class="text-sm text-gray-400 text-center">or <a href="{{ route('register') }}" class="underline">create an account</a></p>
                </div>
                @endauth
            @else
                <p class="text-gray-500 italic">No variants available for this product.</p>
            @endif
        </div>
    </div>
</div>

@endsection