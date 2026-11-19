@props(['title' => 'Admin'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-sm">BW</span>
                        <span class="text-base font-bold text-zinc-800 dark:text-zinc-100">Super Admin Wisata</span>
                    </div>

                    <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-zinc-100 text-zinc-900 font-semibold dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.tenants.index') }}" 
                           class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('admin.tenants.index') || request()->routeIs('admin.tenants.edit') ? 'bg-zinc-100 text-zinc-900 font-semibold dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                            Daftar Destinasi &amp; Tenant
                        </a>
                        <a href="{{ route('admin.tenants.create') }}" 
                           class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('admin.tenants.create') ? 'bg-zinc-100 text-zinc-900 font-semibold dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                            + Tambah Wisata
                        </a>
                        <a href="{{ route('admin.gates.index') }}" 
                           class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('admin.gates.*') ? 'bg-zinc-100 text-zinc-900 font-semibold dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                            Gerbang Tiket
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-zinc-500">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-red-600 hover:underline px-2.5 py-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                                Keluar
                            </button>
                        </form>
                    @endauth
                    @isset($headerRight)
                        {{ $headerRight }}
                    @endisset
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
    @fluxScripts
</body>
</html>
