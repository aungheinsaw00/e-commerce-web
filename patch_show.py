import re

with open('resources/views/admin/orders/show.blade.php', 'r') as f:
    content = f.read()

# 1. Wrap with x-data and add WarningBox template
replacement1 = """<div class="space-y-8" x-data="orderManager({
    orderId: {{ $order->id }},
    status: '{{ $order->status }}',
    hasProof: {{ $order->latestPayment && $order->latestPayment->proof_path ? 'true' : 'false' }},
    paymentStatus: '{{ $order->latestPayment->status ?? 'none' }}'
})">

    <!-- WarningBox Component (Alpine) -->
    <template x-teleport="body">
        <div x-show="warning.show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;" x-transition>
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6" @click.away="warning.show = false">
                <h3 class="text-lg font-bold text-slate-900 mb-2" x-text="warning.title"></h3>
                <p class="text-slate-600 mb-6 text-sm leading-relaxed" x-text="warning.message"></p>
                <div class="flex justify-end gap-3">
                    <button @click="warning.show = false" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition">Cancel</button>
                    <button @click="confirmAction()" x-show="warning.actionUrl" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm" x-text="warning.confirmText"></button>
                    <button @click="warning.show = false" x-show="!warning.actionUrl" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm" x-text="warning.confirmText"></button>
                </div>
            </div>
        </div>
    </template>"""

content = content.replace('<div class="space-y-8">', replacement1)


# 2. Dynamic status badge
badge_old = '<x-admin.status-badge :status="$order->status" />'
badge_new = """<div class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full border border-slate-200/50" 
                 :class="{
                    'bg-amber-50 text-amber-600': status === 'pending_payment' || status === 'pending',
                    'bg-emerald-50 text-emerald-600': status === 'paid' || status === 'completed',
                    'bg-sky-50 text-sky-600': status === 'processing' || status === 'shipped',
                    'bg-rose-50 text-rose-600': status === 'cancelled' || status === 'refunded'
                 }"
                 x-text="status.replace('_', ' ')">
            </div>"""
content = content.replace(badge_old, badge_new)


# 3. Order Action Sequence
update_card_old = """            <!-- Update Order Status Card -->
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
            </x-admin.card>"""

update_card_new = """            <!-- Order Action Sequence -->
            <x-admin.card title="Order Action">
                <div x-show="nextActionLabel" class="space-y-4" style="display: none;">
                    <p class="text-sm text-slate-500">Next logical step for this order:</p>
                    <button @click="promptAction()" :disabled="loading" class="w-full inline-flex justify-center items-center py-2.5 px-4 border border-transparent shadow-sm text-xs font-bold uppercase tracking-wider rounded-xl text-white bg-slate-800 hover:bg-slate-900 focus:outline-none transition disabled:opacity-50">
                        <span x-text="loading ? 'Processing...' : nextActionLabel"></span>
                    </button>
                </div>
                <div x-show="!nextActionLabel" class="text-sm font-medium text-slate-400 italic py-2" style="display: none;">
                    No further actions available for this order status.
                </div>
            </x-admin.card>"""
content = content.replace(update_card_old, update_card_new)


# 4. Remove Awaiting Payment manual button
manual_paid_old = """                    <!-- Manual Mark Paid Actions for pending payment order -->
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
                    @endif"""

manual_paid_new = """                    <template x-if="status === 'pending_payment'">
                        <div class="mb-2">
                            <span class="px-2.5 py-0.5 inline-flex text-xxs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200/50 uppercase tracking-wider">Awaiting Payment</span>
                        </div>
                    </template>"""
content = content.replace(manual_paid_old, manual_paid_new)


# 5. Update verification status text
verif_status_old = """                                <span class="font-extrabold uppercase text-xs tracking-wider {{ $order->latestPayment->status === 'verified' ? 'text-emerald-500' : ($order->latestPayment->status === 'pending' ? 'text-amber-500' : 'text-rose-500') }}">
                                    {{ $order->latestPayment->status }}
                                </span>"""

verif_status_new = """                                <span class="font-extrabold uppercase text-xs tracking-wider"
                                      :class="{
                                        'text-emerald-500': paymentStatus === 'verified',
                                        'text-amber-500': paymentStatus === 'pending',
                                        'text-rose-500': paymentStatus === 'failed' || paymentStatus === 'rejected'
                                      }" x-text="paymentStatus">
                                </span>"""
content = content.replace(verif_status_old, verif_status_new)


# 6. Remove Approve/Reject forms
forms_old = """                                <!-- Review Pending Payment Approval Actions -->
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
                                @endif"""
forms_new = """                                <!-- Approval forms removed, handled by main Order Action button -->"""
content = content.replace(forms_old, forms_new)


# 7. Add Script
script_content = """@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderManager', (initialState) => ({
        ...initialState,
        loading: false,
        warning: {
            show: false,
            title: '',
            message: '',
            confirmText: '',
            actionUrl: '',
            actionMethod: '',
            actionData: null
        },
        
        get nextActionLabel() {
            if (this.status === 'pending_payment') return this.hasProof ? 'Verify Proof' : 'Mark as Paid';
            if (this.status === 'paid') return 'Ship Order';
            if (this.status === 'shipped') return 'Deliver Order';
            return null;
        },
        
        promptAction() {
            if (this.status === 'pending_payment') {
                this.showWarning(
                    this.hasProof ? 'Verify Payment Proof' : 'Mark Order as Paid',
                    this.hasProof ? 'Are you sure you want to approve this payment proof?' : 'Are you sure you want to mark this order as paid without proof?',
                    this.hasProof ? 'Verify' : 'Mark Paid',
                    `/admin/orders/${this.orderId}/process-payment`,
                    'POST'
                );
            } else if (this.status === 'paid') {
                this.showWarning(
                    'Ship Order',
                    'Are you sure you want to mark this order as shipped?',
                    'Ship',
                    `/admin/orders/${this.orderId}/status`,
                    'PATCH',
                    { status: 'shipped' }
                );
            } else if (this.status === 'shipped') {
                this.showWarning(
                    'Deliver Order',
                    'Are you sure you want to mark this order as completed (delivered)?',
                    'Deliver',
                    `/admin/orders/${this.orderId}/status`,
                    'PATCH',
                    { status: 'completed' }
                );
            }
        },

        showWarning(title, message, confirmText, url, method, data = null) {
            this.warning = {
                show: true,
                title,
                message,
                confirmText,
                actionUrl: url,
                actionMethod: method,
                actionData: data
            };
        },

        async confirmAction() {
            if (!this.warning.actionUrl) {
                this.warning.show = false;
                return;
            }
            this.loading = true;
            this.warning.show = false;
            try {
                const response = await fetch(this.warning.actionUrl, {
                    method: this.warning.actionMethod,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: this.warning.actionData ? JSON.stringify(this.warning.actionData) : null
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    this.status = result.status || (this.warning.actionData ? this.warning.actionData.status : this.status);
                    if (this.warning.actionUrl.includes('process-payment')) {
                        this.status = 'paid';
                        this.paymentStatus = 'verified';
                    }
                } else {
                    this.showWarning('Error', result.error || 'An unexpected error occurred.', 'OK', null, null);
                    this.warning.show = true;
                }
            } catch (error) {
                console.error(error);
                this.showWarning('Error', 'Network error occurred.', 'OK', null, null);
                this.warning.show = true;
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
@endpush
"""

content = content.replace('@endsection', script_content)

with open('resources/views/admin/orders/show.blade.php', 'w') as f:
    f.write(content)

