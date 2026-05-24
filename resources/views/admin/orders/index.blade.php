@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6 flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Manage Orders') }}
            </h2>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                
                <!-- Filters -->
                <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-4 mb-6 w-full">
                    <div class="min-w-0 flex-1 md:flex-none">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select name="status" id="status" class="mt-1 block w-full min-w-0 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    @if(request('status'))
                        <a href="{{ route('admin.orders.index') }}" class="text-sm text-red-600 hover:text-red-900 mb-2">Clear Filter</a>
                    @endif
                </form>

                <div class="w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 break-words">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 break-words">
                                        <div class="text-sm text-gray-900">{{ $order->user->name ?? 'Guest' }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->user->email ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $order->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        ${{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 break-words">
                                        @if($order->latestPayment)
                                            <div class="text-sm text-gray-900">{{ ucfirst($order->latestPayment->payment_method) }}</div>
                                            <div class="text-xs">
                                                @if($order->latestPayment->status === 'verified')
                                                    <span class="text-green-600 font-semibold">Verified</span>
                                                @elseif($order->latestPayment->status === 'pending')
                                                    <span class="text-yellow-600 font-semibold">Pending Review</span>
                                                @else
                                                    <span class="text-red-600 font-semibold">{{ ucfirst($order->latestPayment->status) }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 break-words">
                                        @php
                                            $badgeClass = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ';
                                            if(in_array($order->status, ['completed', 'paid', 'shipped'])) $badgeClass .= 'bg-green-100 text-green-800';
                                            elseif(in_array($order->status, ['pending', 'pending_payment', 'processing'])) $badgeClass .= 'bg-yellow-100 text-yellow-800';
                                            else $badgeClass .= 'bg-red-100 text-red-800';
                                        @endphp
                                        <span class="{{ $badgeClass }}">{{ str_replace('_', ' ', $order->status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium break-words">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">View details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No orders found matching the criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
