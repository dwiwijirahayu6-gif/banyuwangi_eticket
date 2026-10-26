<x-layouts.admin title="Tenant & Skema Komisi">
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Daftar Destinasi (Tenant)</h1>
        <a href="{{ route('admin.tenants.create') }}">
            <x-button>+ Daftarkan Tenant</x-button>
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead class="bg-zinc-100 text-xs uppercase tracking-wide text-zinc-600">
                <tr>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Kode</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                    <tr class="border-t border-zinc-200">
                        <td class="px-4 py-3 font-medium">{{ $tenant->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $tenant->code }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$tenant->status === 'active' ? 'active' : 'default'">
                                {{ strtoupper($tenant->status) }}
                            </x-status-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a class="text-sm font-medium underline" href="{{ route('admin.tenants.edit', $tenant) }}">Ubah</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-3 text-center text-zinc-500" colspan="4">Belum ada tenant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>