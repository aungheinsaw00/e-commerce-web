@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-6 flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                Order #{{ $order->id }}
            </h2>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none disabled:opacity-25 transition">
                Back to Orders
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Order Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-lg text-gray-800">Items</h3>
                        @php
                            $badgeClass = 'px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full ';
                            if(in_array($order->status, ['completed', 'paid', 'shipped'])) $badgeClass .= 'bg-green-100 text-green-800';
                            elseif(in_array($order->status, ['pending', 'pending_payment', 'processing'])) $badgeClass .= 'bg-yellow-100 text-yellow-800';
                            else $badgeClass .= 'bg-red-100 text-red-800';
                        @endphp
                        <span class="{{ $badgeClass }}">{{ str_replace('_', ' ', $order->status) }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->product_name_snapshot }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->sku_snapshot }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($item->final_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">${{ number_format($item->final_price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t border-gray-200 flex flex-col items-end space-y-2">
                        <div class="flex justify-between w-64 text-sm text-gray-600">
                            <span>Subtotal:</span>
                            <span>${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_total > 0)
                        <div class="flex justify-between w-64 text-sm text-green-600">
                            <span>Discount:</span>
                            <span>-${{ number_format($order->discount_total, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between w-64 text-lg font-bold text-gray-900 border-t pt-2 mt-2">
                            <span>Total:</span>
                            <span>${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($order->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-lg text-gray-800">Order Notes</h3>
                    </div>
                    <div class="p-6 bg-gray-50 text-gray-700">
                        {{ $order->notes }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-lg text-gray-800">Customer Information</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm text-gray-500">Name</span>
                            <span class="text-sm font-medium text-gray-900">{{ $order->user->name ?? 'Guest' }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm text-gray-500">Email</span>
                            <span class="text-sm font-medium text-gray-900">{{ $order->user->email ?? 'N/A' }}</span>
                        </div>
                        @if($order->user && $order->user->phone_num)
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm text-gray-500">Phone</span>
                            <span class="text-sm font-medium text-gray-900">{{ $order->user->phone_num }}</span>
                        </div>
                        @endif
                        @if($order->user && $order->user->address)
                        <div>
                            <span class="text-sm text-gray-500 block mb-1">Shipping Address</span>
                            <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $order->user->address }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Status Update -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-lg text-gray-800">Update Status</h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <select name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="pending_payment" {{ $order->status == 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                                    <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="refunded" {{ $order->status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Payment Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-semibold text-lg text-gray-800">Payment Information</h3>
                    </div>
                    <div class="p-6">
                        @if($order->status === 'pending_payment')
                            <div class="mb-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 mb-2">Awaiting Payment</span>
                            </div>
                            <form action="{{ route('admin.orders.markPaid', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this order as paid?');">
                                @csrf
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition">
                                    Mark as Paid
                                </button>
                            </form>
                        @endif

                        @if($order->latestPayment)
                            <div class="space-y-3 mt-4">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-sm text-gray-500">Method</span>
                                    <span class="text-sm font-medium text-gray-900">{{ ucfirst($order->latestPayment->payment_method ?? $order->latestPayment->provider ?? 'Unknown') }}</span>
                                </div>
                                @if($order->latestPayment->transaction_ref)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-sm text-gray-500">Transaction Ref</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $order->latestPayment->transaction_ref }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <span class="text-sm font-bold {{ $order->latestPayment->status === 'verified' ? 'text-green-600' : ($order->latestPayment->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ ucfirst($order->latestPayment->status) }}
                                    </span>
                                </div>
                            </div>

                            @if($order->latestPayment->proof_path)
                                <div class="mt-6 border-t pt-4">
                                    <span class="block text-sm font-medium text-gray-700 mb-2">Payment Proof</span>
                                    <span class="hidden">{{ $order->latestPayment->proof_path }}</span>
                                    <div class="mb-4 flex justify-center" x-data="{ zoomOpen: false }">
                                        <!-- Inline payment proof image -->
                                        <img src="{{ route('admin.payments.proof', $order->latestPayment) }}" alt="Payment Proof" class="max-w-xs w-full object-contain rounded-xl shadow-md border border-gray-200 cursor-pointer hover:opacity-90 transition hover:shadow-lg" @click="zoomOpen = true">
                                        
                                        <!-- Zoom Modal -->
                                        <div x-show="zoomOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.stop="zoomOpen = false">
                                            <img src="{{ route('admin.payments.proof', $order->latestPayment) }}" alt="Payment Proof Zoomed" class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl" @click.stop>
                                            <button @click="zoomOpen = false" class="absolute top-6 right-6 text-white hover:text-gray-300 transition-colors bg-black/50 rounded-full p-1">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                    @if($order->latestPayment->status === 'pending')
                                        <div class="flex space-x-3">
                                            <form action="{{ route('admin.payments.verify', $order->latestPayment) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">Verify</button>
                                            </form>
                                            <form action="{{ route('admin.payments.reject', $order->latestPayment) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Reject</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @elseif($order->status !== 'pending_payment')
                            <div class="mt-4 text-sm text-gray-500 italic">
                                No payment details recorded yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
