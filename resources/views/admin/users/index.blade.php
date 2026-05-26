@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Customers</h1>
            <p class="text-sm text-slate-500 mt-1">Browse and inspect individual customer profiles.</p>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-3">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search by name or email…"
               class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-400/40 bg-white shadow-sm">
        <button type="submit"
                class="px-5 py-2.5 rounded-xl bg-sky-500 text-white text-sm font-semibold hover:bg-sky-600 transition shadow-sm">
            Search
        </button>
        @if(request('search'))
            <a href="{{ route('admin.users.index') }}"
               class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <x-admin.card title="All Customers">
        <x-slot name="action">
            <span class="text-xs font-semibold text-slate-400">{{ $users->total() }} customers</span>
        </x-slot>

        <x-admin.data-table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-left">Customer</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-left">Email</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-left">Joined</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                    @if($user->phone_num)
                                        <p class="text-xs text-slate-400">{{ $user->phone_num }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.users.show', $user->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-sky-600 bg-sky-50 hover:bg-sky-100 transition border border-sky-100">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View Profile
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                No customers found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </x-admin.card>
</div>
@endsection
