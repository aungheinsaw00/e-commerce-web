@extends('layouts.app')
@section('title', 'Manage Orders')
@section('content')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Orders</h1>

    <form method="GET" class="mb-6 flex gap-3">
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="pending_payment" {{ request('status') === 'pending_payment' ? 'selected' : '' }}>Pending payment</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
            <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </form>

    <div class="space-y-3">
        @forelse($orders as $order)
        @php $statusLabel = str_replace('_', ' ', $order->status); @endphp
        <a href="{{ route('admin.orders.show', $order) }}"
           class="block bg-white rounded-xl border border-gray-100 p-5 hover:border-gray-300 transition">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="font-semibold text-gray-900">#{{ $order->id }}</span>
                    <span class="text-sm text-gray-500 ml-2">{{ $order->user->name }}</span>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="font-bold">${{ number_format($order->total, 2) }}</span>
                    <span class="capitalize text-gray-600">{{ $statusLabel }}</span>
                    @if($order->paymentMethod)
                    <span class="text-gray-400">{{ $order->paymentMethod->name }}</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <p class="text-gray-500 text-sm">No orders found.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $orders->links() }}</div>
</div>

@endsection
