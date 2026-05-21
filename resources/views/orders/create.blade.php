@extends('layouts.app')
@section('title', 'Checkout')
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Checkout</h1>

    @if(isset($summary) && count($summary['items']))

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Checkout Form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('orders.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Order Notes --}}
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Notes</h2>
                    <textarea name="notes" rows="3" placeholder="Special instructions or notes (optional)"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 resize-none">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Payment Proof --}}
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="font-semibold text-gray-900 mb-1">Payment Proof</h2>
                    <p class="text-xs text-gray-400 mb-4">Please upload a screenshot or photo of your bank transfer receipt.</p>
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-xl px-6 py-8 cursor-pointer hover:border-gray-400 transition" for="payment_proof">
                        <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-500">Click to upload payment proof</span>
                        <span class="text-xs text-gray-400 mt-1">JPG, PNG, GIF up to 5MB</span>
                    </label>
                    <input type="file" id="payment_proof" name="payment_proof" accept="image/*" class="sr-only"
                        onchange="document.getElementById('file-name').textContent = this.files[0]?.name || ''">
                    <p id="file-name" class="text-xs text-gray-500 mt-2 text-center"></p>
                    @error('payment_proof') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    class="w-full bg-gray-900 text-white font-semibold py-3 rounded-xl hover:bg-gray-700 transition">
                    Place Order — ${{ number_format($summary['total'], 2) }}
                </button>
            </form>
        </div>

        {{-- Order Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                <h2 class="font-semibold text-gray-900 mb-4">Your Items</h2>
                <div class="space-y-3 divide-y divide-gray-50">
                    @foreach($summary['items'] as $item)
                    <div class="flex justify-between items-start pt-3 first:pt-0 text-sm">
                        <div class="flex-1 min-w-0 pr-3">
                            <p class="font-medium text-gray-900 line-clamp-1">{{ $item['product']->name }}</p>
                            <p class="text-xs text-gray-400">× {{ $item['quantity'] }}</p>
                        </div>
                        <span class="font-semibold text-gray-900 shrink-0">${{ number_format($item['final_line_total'], 2) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>Subtotal</span>
                        <span>${{ number_format($summary['subtotal'], 2) }}</span>
                    </div>
                    @if($summary['discount_total'] > 0)
                    <div class="flex justify-between text-red-500">
                        <span>Discount</span>
                        <span>− ${{ number_format($summary['discount_total'], 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-100 pt-2">
                        <span>Total</span>
                        <span>${{ number_format($summary['total'], 2) }}</span>
                    </div>
                </div>

                <a href="{{ route('cart.index') }}"
                   class="block mt-4 text-center text-sm text-gray-400 hover:text-gray-600 transition">
                    ← Edit Cart
                </a>
            </div>
        </div>
    </div>

    @else
    <div class="text-center py-20 bg-white rounded-xl border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Your cart is empty</h2>
        <a href="{{ route('products.index') }}" class="inline-block mt-4 bg-gray-900 text-white px-6 py-2.5 rounded-xl hover:bg-gray-700 transition font-medium">
            Browse Products
        </a>
    </div>
    @endif
</div>

@endsection
