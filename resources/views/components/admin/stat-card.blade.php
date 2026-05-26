@props([
    'title',
    'value',
    'href' => '#',
    'color' => 'sky', // sky, emerald, amber, rose, slate
])

@php
    $colorMap = [
        'sky' => [
            'bg' => 'bg-sky-50',
            'text' => 'text-sky-600',
            'border' => 'border-sky-500',
            'glow' => 'group-hover:shadow-sky-100/50',
        ],
        'emerald' => [
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-600',
            'border' => 'border-emerald-500',
            'glow' => 'group-hover:shadow-emerald-100/50',
        ],
        'amber' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-600',
            'border' => 'border-amber-500',
            'glow' => 'group-hover:shadow-amber-100/50',
        ],
        'rose' => [
            'bg' => 'bg-rose-50',
            'text' => 'text-rose-600',
            'border' => 'border-rose-500',
            'glow' => 'group-hover:shadow-rose-100/50',
        ],
        'slate' => [
            'bg' => 'bg-slate-50',
            'text' => 'text-slate-600',
            'border' => 'border-slate-500',
            'glow' => 'group-hover:shadow-slate-100/50',
        ]
    ];
    $c = $colorMap[$color] ?? $colorMap['sky'];
@endphp

<a href="{{ $href }}" class="group block h-full">
    <div class="h-full bg-white rounded-2xl border border-slate-100 p-6 flex items-center justify-between shadow-sm shadow-slate-100/40 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $c['glow'] }} border-l-4 {{ $c['border'] }}">
        <div class="min-w-0 flex-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-1">{{ $title }}</span>
            <span class="text-2xl font-bold text-slate-800 tracking-tight block truncate">{{ $value }}</span>
        </div>
        
        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ml-4 transition-all duration-300 {{ $c['bg'] }} {{ $c['text'] }} group-hover:scale-110">
            @if($slot->isEmpty())
                <!-- Default fallback icon -->
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</a>
