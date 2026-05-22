@extends('layouts.app')
@section('title', 'Order #' . $order->id)
@section('content')

@php
    $statusLabel = str_replace('_', ' ', $order->status);
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
            <p class="text-sm text-gray-500">{{ $order->user->name }} · {{ $order->created_at->format('M j, Y g:i A') }}</p>
        </div>
        <span class="inline-block text-sm font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-700 capitalize">
            {{ $statusLabel }}
        </span>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Order Total</h2>
            <p class="text-2xl font-bold">${{ number_format($order->total, 2) }}</p>
            <p class="text-sm text-gray-500 mt-1">Subtotal: ${{ number_format($order->subtotal, 2) }}
                @if($order->discount_total > 0) · Discount: −${{ number_format($order->discount_total, 2) }}@endif
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Customer Contact</h2>
            <p class="text-sm text-gray-700"><span class="font-medium">Phone:</span> {{ $order->user->phone_num ?: '—' }}</p>
            <p class="text-sm text-gray-700 mt-2"><span class="font-medium">Address:</span></p>
            <p class="text-sm text-gray-600 whitespace-pre-line">{{ $order->user->address ?: '—' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-3">Payment</h2>
        @if($order->paymentMethod)
        <p class="text-sm text-gray-700"><span class="font-medium">Method:</span> {{ $order->paymentMethod->name }}</p>
        @endif
        @if($order->latestPayment)
        <p class="text-sm text-gray-500 mt-1">Record: {{ $order->latestPayment->status }} ({{ $order->latestPayment->provider }})</p>
        @if($order->latestPayment->proof_path)
        <a href="{{ route('admin.payments.proof', $order->latestPayment) }}"
           class="inline-block mt-3 text-sm font-medium text-blue-600 hover:underline"
           target="_blank" rel="noopener">
            View payment proof
        </a>
        @else
        <p class="text-sm text-amber-600 mt-2">No payment proof uploaded yet.</p>
        @endif
        @endif
    </div>

    @if($order->status === 'pending_payment')
    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Payment Actions</h2>
        <form method="POST" action="{{ route('admin.orders.markPaid', $order) }}" class="inline">
            @csrf
            <button type="submit"
                class="bg-green-600 text-white font-medium px-5 py-2.5 rounded-lg hover:bg-green-700 transition"
                onclick="return confirm('Mark this order as paid? Stock will be deducted.')">
                Mark as Paid
            </button>
        </form>
        @if($order->latestPayment)
        <form method="POST" action="{{ route('admin.payments.reject', $order->latestPayment) }}" class="inline ml-3">
            @csrf
            <button type="submit"
                class="border border-red-300 text-red-600 font-medium px-5 py-2.5 rounded-lg hover:bg-red-50 transition"
                onclick="return confirm('Reject payment and cancel this order?')">
                Cancel Order
            </button>
        </form>
        @endif
    </div>
    @endif

    @if(in_array($order->status, ['paid', 'processing', 'shipped']))
    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Update Fulfillment Status</h2>
        <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            @method('PATCH')
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="completed">Completed</option>
            </select>
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">Update</button>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-4">Items</h2>
        @foreach($order->orderItems as $item)
        <div class="flex justify-between py-2 border-b border-gray-50 last:border-0 text-sm">
            <span>{{ $item->product_name_snapshot }} ({{ $item->sku_snapshot }}) × {{ $item->quantity }}</span>
            <span class="font-medium">${{ number_format($item->final_price * $item->quantity, 2) }}</span>
        </div>
        @endforeach
    </div>

    @if($order->notes)
    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-2">Customer Notes</h2>
        <p class="text-sm text-gray-600">{{ $order->notes }}</p>
    </div>
    @endif

    <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Orders</a>
</div>

@endsection
