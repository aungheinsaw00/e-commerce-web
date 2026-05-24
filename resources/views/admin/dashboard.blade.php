@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('admin.orders.index') }}" class="block hover:shadow-lg transition h-full">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-gray-400 h-full">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Orders</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</div>
                </div>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="block hover:shadow-lg transition h-full">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-400 h-full">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Orders</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</div>
                </div>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="block hover:shadow-lg transition h-full">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-400 h-full">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Revenue</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">${{ number_format($stats['revenue'], 2) }}</div>
                </div>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="block hover:shadow-lg transition h-full">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-400 h-full">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Payments</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['pending_payments'] }}</div>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-lg text-gray-800">Recent Orders</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentOrders->sortByDesc('created_at') as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium break-words">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">#{{ $order->id }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 break-words">{{ $order->user->name ?? 'Guest' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">${{ $order->total }}</td>
                                    <td class="px-6 py-4 break-words">
                                        @php
                                            $badgeClass = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ';
                                            if(in_array($order->status, ['completed', 'paid', 'shipped'])) $badgeClass .= 'bg-green-100 text-green-800';
                                            elseif(in_array($order->status, ['pending', 'pending_payment', 'processing'])) $badgeClass .= 'bg-yellow-100 text-yellow-800';
                                            else $badgeClass .= 'bg-red-100 text-red-800';
                                        @endphp
                                        <span class="{{ $badgeClass }}">{{ str_replace('_', ' ', $order->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No recent orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-lg text-red-600 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Low Stock Alerts
                    </h3>
                    <a href="{{ route('admin.products.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Manage Stock</a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product / Variant</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($lowStock as $inventory)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 break-words">
                                        <div class="font-medium">{{ $inventory->variant->product->name }}</div>
                                        <div class="text-gray-500">{{ $inventory->variant->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 break-words">{{ $inventory->variant->sku }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $inventory->stock_quantity }} left
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-sm text-gray-500 text-center">No low stock alerts.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
