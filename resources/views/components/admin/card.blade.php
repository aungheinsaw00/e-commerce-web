@props(['title' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100/40 overflow-hidden']) }}>
    @if($title || $action)
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            @if($title)
                <h3 class="font-semibold text-slate-800 text-base">{{ $title }}</h3>
            @endif
            @if($action)
                <div class="shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
