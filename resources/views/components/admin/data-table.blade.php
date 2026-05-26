<div class="w-full overflow-hidden bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100/40">
    <div class="w-full overflow-x-auto scrollbar-thin scrollbar-thumb-slate-200">
        <table {{ $attributes->merge(['class' => 'w-full min-w-[600px] border-collapse text-left text-sm text-slate-600']) }}>
            {{ $slot }}
        </table>
    </div>
</div>
