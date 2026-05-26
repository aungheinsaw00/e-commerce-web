@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="space-y-8">
    
    <!-- Top Action bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm shadow-slate-100/40">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Order #{{ $order->id }}</h1>
                <p class="text-sm text-slate-500 mt-1">Placed on {{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            <x-admin.status-badge :status="$order->status" />
        </div>
        <div class="shrink-0">
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl font-medium text-sm transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Orders
            </a>
        </div>
    </div>

    <!-- Main 3-Column Responsive Grid (2-span for details, 1-span for side sidebar widgets) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Items list & Notes (Spans 2 columns) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Order Items Card -->
            <x-admin.card title="Order Line Items">
                <x-admin.data-table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">Product Item</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">SKU</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-center">Qty</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-right">Price</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($order->orderItems as $item)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $item->product_name_snapshot }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-mono font-medium">
                                    {{ $item->sku_snapshot }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-semibold text-center">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium text-right">
                                    ${{ number_format($item->final_price, 2) }}
                                </td>
                                <td class="px-6 py-4 font-extrabold text-slate-800 text-right">
                                    ${{ number_format($item->final_price * $item->quantity, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-admin.data-table>

                <!-- Order Totals summary block -->
                <div class="mt-6 border-t border-slate-100 pt-6 flex flex-col items-end gap-2.5 text-sm font-medium text-slate-500">
                    <div class="flex justify-between w-64">
                        <span>Subtotal</span>
                        <span class="text-slate-700 font-semibold">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_total > 0)
                        <div class="flex justify-between w-64 text-emerald-600 font-semibold">
                            <span>Discount Total</span>
                            <span>-${{ number_format($order->discount_total, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between w-64 text-base font-extrabold text-slate-800 border-t border-slate-100 pt-3 mt-1.5">
                        <span>Grand Total</span>
                        <span class="text-slate-900">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </x-admin.card>

            <!-- Order Notes Card -->
            @if($order->notes)
                <x-admin.card title="Customer Notes">
                    <div class="p-4 bg-slate-50 border border-slate-100 text-slate-600 rounded-xl text-sm font-medium leading-relaxed">
                        {{ $order->notes }}
                    </div>
                </x-admin.card>
            @endif

        </div>

        <!-- Right: Status Update, Customer Info, Payment Details -->
        <div class="space-y-8">
            
            <!-- Customer Information Card -->
            <x-admin.card title="Customer Information">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 text-sm">
                        <span class="text-slate-400 font-semibold">Name</span>
                        <span class="text-slate-800 font-bold">{{ $order->user->name ?? 'Guest' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 text-sm">
                        <span class="text-slate-400 font-semibold">Email</span>
                        <span class="text-slate-800 font-bold truncate max-w-[160px]" title="{{ $order->user->email ?? 'N/A' }}">{{ $order->user->email ?? 'N/A' }}</span>
                    </div>
                    @if($order->user && $order->user->phone_num)
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 text-sm">
                            <span class="text-slate-400 font-semibold">Phone</span>
                            <span class="text-slate-800 font-bold">{{ $order->user->phone_num }}</span>
                        </div>
                    @endif
                    @if($order->user && $order->user->address)
                        <div class="pt-1">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Shipping Address</span>
                            <div class="text-xs text-slate-600 font-medium bg-slate-50 border border-slate-100 p-3.5 rounded-xl leading-relaxed">
                                {{ $order->user->address }}
                            </div>
                        </div>
                    @endif
                </div>
            </x-admin.card>

            <!-- Update Order Status Card -->
            <x-admin.card title="Delivery Status Action">
                <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="status-update" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Order Status</label>
                        <select name="status" id="status-update" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-semibold bg-white focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition shadow-sm" required>
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
                    <button type="submit" class="w-full inline-flex justify-center items-center py-2.5 px-4 border border-transparent shadow-sm text-xs font-bold uppercase tracking-wider rounded-xl text-white bg-slate-800 hover:bg-slate-900 focus:outline-none transition">
                        Update Status
                    </button>
                </form>
            </x-admin.card>

            <!-- Payment Information & Management -->
            <x-admin.card title="Payment Details">
                <div class="space-y-4">
                    <!-- Manual Mark Paid Actions for pending payment order -->
                    @if($order->status === 'pending_payment')
                        <div class="mb-2">
                            <span class="px-2.5 py-0.5 inline-flex text-xxs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200/50 uppercase tracking-wider">Awaiting Payment</span>
                        </div>
                        <form action="{{ route('admin.orders.markPaid', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this order as paid?');">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center py-2.5 px-4 border border-transparent shadow-sm text-xs font-bold uppercase tracking-wider rounded-xl text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none transition">
                                Mark as Paid
                            </button>
                        </form>
                    @endif

                    @if($order->latestPayment)
                        <div class="space-y-3.5 text-sm">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-400 font-semibold">Payment Method</span>
                                <span class="text-slate-800 font-bold">{{ ucfirst($order->latestPayment->payment_method ?? $order->latestPayment->provider ?? 'Unknown') }}</span>
                            </div>
                            @if($order->latestPayment->transaction_ref)
                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span class="text-slate-400 font-semibold">Transaction Ref</span>
                                    <span class="text-slate-800 font-mono font-bold">{{ $order->latestPayment->transaction_ref }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-400 font-semibold">Verification Status</span>
                                <span class="font-extrabold uppercase text-xs tracking-wider {{ $order->latestPayment->status === 'verified' ? 'text-emerald-500' : ($order->latestPayment->status === 'pending' ? 'text-amber-500' : 'text-rose-500') }}">
                                    {{ $order->latestPayment->status }}
                                </span>
                            </div>
                        </div>

                        <!-- Payment Proof Upload Area -->
                        @if($order->latestPayment->proof_path)
                            <div class="mt-5 border-t border-slate-100 pt-4" x-data="{ zoomOpen: false }">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">Payment Receipt</span>
                                <span class="hidden">{{ $order->latestPayment->proof_path }}</span>
                                
                                <div class="flex justify-center">
                                    <!-- Receipt Image Preview -->
                                    <img src="{{ route('admin.payments.proof', $order->latestPayment) }}" 
                                         alt="Payment Receipt" 
                                         class="max-w-xs w-full object-contain rounded-xl shadow-sm border border-slate-100 cursor-pointer hover:opacity-90 hover:shadow-md transition duration-200" 
                                         @click="zoomOpen = true">
                                    
                                    <!-- Zoom Modal Overlay (Alpine.js) -->
                                    <div x-show="zoomOpen" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         style="display: none;" 
                                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" 
                                         @click.stop="zoomOpen = false">
                                        <img src="{{ route('admin.payments.proof', $order->latestPayment) }}" 
                                             alt="Payment Receipt Zoomed" 
                                             class="max-h-[85vh] max-w-[85vw] object-contain rounded-xl shadow-2xl" 
                                             @click.stop>
                                        <button @click="zoomOpen = false" 
                                                class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-2.5 focus:outline-none">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Review Pending Payment Approval Actions -->
                                @if($order->latestPayment->status === 'pending')
                                    <div class="flex gap-3 mt-5">
                                        <form action="{{ route('admin.payments.verify', $order->latestPayment) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full inline-flex justify-center items-center py-2 px-3 border border-transparent shadow-sm text-xs font-bold uppercase tracking-wider rounded-xl text-white bg-emerald-500 hover:bg-emerald-600 transition">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.payments.reject', $order->latestPayment) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full inline-flex justify-center items-center py-2 px-3 border border-transparent shadow-sm text-xs font-bold uppercase tracking-wider rounded-xl text-white bg-rose-500 hover:bg-rose-600 transition">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @elseif($order->status !== 'pending_payment')
                        <div class="text-xs font-medium text-slate-400 italic text-center py-4 bg-slate-50/50 border border-dashed border-slate-100 rounded-xl">
                            No payment records found yet.
                        </div>
                    @endif

                    @if($order->payment_proof)
                        <div class="mt-4">
                            <img src="{{ asset($order->payment_proof) }}" class="rounded-xl shadow border border-slate-100" />
                        </div>
                    @endif
                </div>
            </x-admin.card>

        </div>

    </div>
</div>
@endsection
