@props(['status'])

@php
    $normalized = strtolower(trim($status));
    $badgeClass = 'px-2.5 py-1 text-xs font-semibold rounded-full inline-flex items-center gap-1.5 shrink-0 uppercase tracking-wider ';
    
    if (in_array($normalized, ['completed', 'paid', 'shipped', 'active', 'success', 'delivered', 'in_stock'])) {
        $badgeClass .= 'bg-emerald-50 text-emerald-700 border border-emerald-200/50';
        $dotColor = 'bg-emerald-500';
    } elseif (in_array($normalized, ['pending', 'pending_payment', 'processing', 'warning', 'low_stock'])) {
        $badgeClass .= 'bg-amber-50 text-amber-700 border border-amber-200/50';
        $dotColor = 'bg-amber-500';
    } elseif (in_array($normalized, ['cancelled', 'failed', 'out_of_stock', 'danger', 'inactive', 'refunded'])) {
        $badgeClass .= 'bg-rose-50 text-rose-700 border border-rose-200/50';
        $dotColor = 'bg-rose-500';
    } else {
        $badgeClass .= 'bg-slate-50 text-slate-600 border border-slate-200/50';
        $dotColor = 'bg-slate-400';
    }
@endphp

<span class="{{ $badgeClass }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} shrink-0"></span>
    {{ str_replace('_', ' ', $slot->isEmpty() ? $status : $slot) }}
</span>
