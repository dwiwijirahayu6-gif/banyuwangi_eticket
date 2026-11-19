@props(['title' => 'Pengelola Destinasi Wisata', 'tenant' => null])

@php
$currentTenant = $tenant ?? request()->route('tenant');
if (is_string($currentTenant) && class_exists(\App\Models\Tenant::class)) {
$resolved = \App\Models\Tenant::where('code', $currentTenant)->orWhere('id', $currentTenant)->first();
if ($resolved) {
$currentTenant = $resolved;
}
}
$tenantCode = ($currentTenant instanceof \App\Models\Tenant) ? $currentTenant->code : ($currentTenant ?? 'demo');
$tenantName = ($currentTenant instanceof \App\Models\Tenant) ? $currentTenant->display_name : 'Pengelola Wisata';
$user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans">
    <div x-data="{ open: false }" class="flex min-h-screen">
        {{-- Sidebar Mobile Overlay --}}
        <div x-show="open"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"
            @click="open = false"
            style="display: none;"></div>

        {{-- Sidebar --}}
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 transform border-r border-slate-200 bg-white p-5 transition-transform duration-200 lg:static lg:translate-x-0 flex flex-col justify-between shadow-sm">
            <div>
                {{-- Logo & Brand --}}
                <div class="mb-6 flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white font-bold shadow-md shadow-blue-500/20">
                            BW
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-900 tracking-tight leading-none">Admin Tenant</span>
                            <span class="text-xs text-slate-500 font-medium">Banyuwangi E-Ticket</span>
                        </div>
                    </div>
                    <button type="button" @click="open = false" class="lg:hidden text-slate-400 hover:text-slate-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Tenant Badge --}}
                <div class="mb-5 rounded-xl bg-blue-50/80 p-3 border border-blue-100/80">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-blue-600">Destinasi Terdaftar</p>
                    <p class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ $tenantName }}</p>
                    <p class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $tenantCode }}</p>
                </div>

                {{-- Navigation --}}
                <nav class="space-y-1 text-sm font-medium">
                    <a href="{{ route('tenant.dashboard', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('tenant.destinasi', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.destinasi*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Profil Destinasi</span>
                    </a>

                    <a href="{{ route('tenant.ticket-manager', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.ticket-manager*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <span>Manajemen Tiket</span>
                    </a>

                    <a href="{{ route('tenant.jadwal-kuota', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.jadwal-kuota*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Jadwal & Kuota</span>
                    </a>

                    <a href="{{ route('tenant.reservasi', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.reservasi*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Reservasi</span>
                    </a>

                    <a href="{{ route('tenant.validasi-tiket', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.validasi-tiket*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Validasi Tiket</span>
                    </a>

                    <a href="{{ route('tenant.laporan', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.laporan*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Laporan Penjualan</span>
                    </a>

                    <a href="{{ route('tenant.saldo-penarikan', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.saldo-penarikan*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Saldo & Penarikan</span>
                    </a>

                    <a href="{{ route('tenant.profil', ['tenant' => $tenantCode]) }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs('tenant.profil*') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Pengaturan Akun</span>
                    </a>
                </nav>
            </div>

            {{-- Footer Sidebar: User & Logout --}}
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center gap-3 mb-3 px-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-slate-700 font-semibold text-xs">
                        {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-slate-900 truncate">{{ $user->name ?? 'Operator' }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ $user->email ?? '' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Wrapper --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Top Header --}}
            <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-slate-200 bg-white/95 backdrop-blur px-6 shadow-xs">
                <div class="flex items-center gap-3">
                    <button type="button" @click="open = !open"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 lg:hidden shadow-xs"
                        aria-label="Toggle sidebar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 leading-tight">{{ $title }}</h1>
                        <p class="text-xs text-slate-500 hidden sm:block">Panel Pengelolaan Operasional Wisata</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Aktif
                    </span>

                    @isset($headerRight)
                    <div>{{ $headerRight }}</div>
                    @endisset
                </div>
            </header>

            {{-- Flash Messages --}}
            <div class="px-6 pt-6 -mb-2">
                @if (session('success'))
                <div class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 shadow-xs mb-4">
                    <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if (session('error'))
                <div class="flex items-center gap-3 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 shadow-xs mb-4">
                    <svg class="h-5 w-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif
            </div>

            {{-- Main Content Slot --}}
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @fluxScripts
</body>

</html>