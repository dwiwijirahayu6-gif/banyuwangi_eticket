<x-layouts.admin title="Manajemen Destinasi &amp; Tenant">
    @if (session('status'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Daftar Destinasi &amp; Tenant</h1>
            <p class="text-sm text-zinc-500">Kelola seluruh mitra pengelola wisata, destinasi, akun admin tenant, dan status operasional.</p>
        </div>
        <div>
            <a href="{{ route('admin.tenants.create') }}" 
               class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg shadow-sm transition gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Destinasi Wisata
            </a>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 mb-6 shadow-sm">
        <form method="GET" action="{{ route('admin.tenants.index') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-3 items-center">
            <div class="sm:col-span-2 md:col-span-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama destinasi, pengelola, kode, atau email admin..."
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <svg class="w-4 h-4 text-zinc-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <div>
                <select name="status" 
                        class="w-full py-2 px-3 text-sm rounded-lg border border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan (Suspended)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 bg-zinc-800 hover:bg-zinc-900 text-white text-sm font-medium rounded-lg transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.tenants.index') }}" 
                       class="px-3 py-2 text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Data --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[56rem] text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-semibold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="px-5 py-3.5">Destinasi Wisata</th>
                        <th class="px-5 py-3.5">Nama Tenant</th>
                        <th class="px-5 py-3.5">Lokasi</th>
                        <th class="px-5 py-3.5">Admin Tenant</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5">Terdaftar</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse ($tenants as $tenant)
                        @php
                            $destination = $tenant->destination;
                            $adminUser = $tenant->users->first();
                        @endphp
                        <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/30 transition">
                            {{-- Destinasi --}}
                            <td class="px-5 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $destination->name ?? $tenant->name }}
                                </div>
                                @if($destination?->slug)
                                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-mono">
                                        /wisata/{{ $destination->slug }}
                                    </div>
                                @endif
                            </td>

                            {{-- Tenant & Kode --}}
                            <td class="px-5 py-4">
                                <div class="text-zinc-800 dark:text-zinc-200 font-medium">{{ $tenant->name }}</div>
                                <span class="inline-block font-mono text-[11px] bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 px-1.5 py-0.5 rounded">
                                    {{ $tenant->code }}
                                </span>
                            </td>

                            {{-- Lokasi --}}
                            <td class="px-5 py-4 text-zinc-600 dark:text-zinc-400 text-xs">
                                {{ $destination->location ?? '-' }}
                            </td>

                            {{-- Admin Tenant --}}
                            <td class="px-5 py-4">
                                @if($adminUser)
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200 text-xs">{{ $adminUser->name }}</div>
                                    <div class="text-[11px] text-zinc-500 font-mono">{{ $adminUser->email }}</div>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Belum ada akun</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 text-center">
                                @if($tenant->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        AKTIF
                                    </span>
                                @elseif($tenant->status === 'suspended')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        SUSPENDED
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-5 py-4 text-xs text-zinc-500">
                                {{ $tenant->created_at?->format('d M Y') ?? '-' }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.tenants.edit', $tenant) }}" 
                                       class="px-2.5 py-1.5 rounded-md text-xs font-medium bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 transition">
                                        Detail &amp; Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.tenants.status', $tenant) }}" class="inline">
                                        @csrf
                                        @if($tenant->status === 'active')
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" 
                                                    onclick="return confirm('Nonaktifkan tenant ini dari katalog publik?')"
                                                    class="px-2.5 py-1.5 rounded-md text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" 
                                                    class="px-2.5 py-1.5 rounded-md text-xs font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition">
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-zinc-500">
                                Tidak ada data destinasi / tenant yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/40">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
