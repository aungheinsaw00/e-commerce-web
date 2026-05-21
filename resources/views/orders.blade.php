@extends('layouts.app')
@section('title', 'My Orders')
@section('content')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
        <a href="{{ route('products.index') }}"
           class="text-sm border border-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Continue Shopping
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-20 bg-white rounded-xl border border-gray-100">
            <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-700 mb-2">No orders yet</h2>
            <p class="text-sm text-gray-400 mb-6">Place your first order to see it here.</p>
            <a href="{{ route('products.index') }}"
               class="inline-block bg-gray-900 text-white font-medium px-6 py-2.5 rounded-xl hover:bg-gray-700 transition">
                Browse Products
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
            @php
                $statusColors = [
                    'pending'    => 'bg-yellow-100 text-yellow-700',
                    'paid'       => 'bg-blue-100 text-blue-700',
                    'processing' => 'bg-purple-100 text-purple-700',
                    'shipped'    => 'bg-indigo-100 text-indigo-700',
                    'completed'  => 'bg-green-100 text-green-700',
                    'cancelled'  => 'bg-red-100 text-red-700',
                    'refunded'   => 'bg-gray-100 text-gray-600',
                ];
                $badge = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600';
            @endphp
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <span class="text-xs text-gray-400">Order #{{ $order->id }}</span>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('M j, Y · g:i A') }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-lg font-bold text-gray-900">${{ number_format($order->total, 2) }}</span>
                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full {{ $badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                {{-- Order Items Preview --}}
                @if($order->orderItems->isNotEmpty())
                <div class="border-t border-gray-50 pt-3 mt-3">
                    <div class="space-y-1">
                        @foreach($order->orderItems->take(3) as $item)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span class="line-clamp-1">{{ $item->product_name ?? 'Product' }} × {{ $item->quantity }}</span>
                            <span>${{ number_format($item->line_total, 2) }}</span>
                        </div>
                        @endforeach
                        @if($order->orderItems->count() > 3)
                        <p class="text-xs text-gray-400">+{{ $order->orderItems->count() - 3 }} more item(s)</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Payment Status --}}
                @if($order->latestPayment)
                <div class="mt-3 pt-3 border-t border-gray-50 text-xs text-gray-500 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Payment: <span class="font-medium capitalize">{{ $order->latestPayment->status ?? 'pending' }}</span>
                    ({{ $order->latestPayment->method ?? 'transfer' }})
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>

@endsection