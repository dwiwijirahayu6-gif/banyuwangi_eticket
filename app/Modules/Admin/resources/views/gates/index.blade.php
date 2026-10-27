<x-layouts.admin title="Gerbang & QR Code">
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap items-baseline gap-2">
        <h1 class="text-xl font-semibold">Gerbang &amp; QR Code — Tiket Wisata</h1>
    </div>

    <form method="POST" action="{{ route('admin.gates.store') }}" class="mt-4 flex flex-wrap items-end gap-2">
        @csrf
        <div class="w-64">
            <label class="text-sm font-medium text-zinc-700">Pilih Destinasi</label>
            <select name="destination_id" class="mt-1 min-h-11 w-full rounded-lg border border-zinc-300 px-3 text-sm">
                @foreach($destinations as $dest)
                    <option value="{{ $dest->id }}">{{ $dest->name }}</option>
                @endforeach
            </select>
        </div>
        <x-input name="code" label="Kode (unik/destinasi)" required />
        <x-input name="label" label="Nama Gerbang" required />
        <x-input name="zone" label="Zona/Lokasi (opsional)" />
        <x-button type="submit">Tambah Gerbang</x-button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead class="bg-zinc-100 text-xs uppercase tracking-wide text-zinc-600">
                <tr>
                    <th class="px-4 py-2">Destinasi</th>
                    <th class="px-4 py-2">Gerbang</th>
                    <th class="px-4 py-2">Kode</th>
                    <th class="px-4 py-2">Status QR</th>
                    <th class="px-4 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gates as $gate)
                    <tr class="border-t border-zinc-200">
                        <td class="px-4 py-3 font-medium">{{ $gate->destination->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            {{ $gate->label }}
                            @if($gate->zone) <span class="text-xs text-zinc-500">({{ $gate->zone }})</span> @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $gate->code }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$gate->activeToken ? 'active' : 'pending'">
                                {{ $gate->activeToken ? 'AKTIF' : 'BELUM ADA' }}
                            </x-status-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.gates.rotate', $gate) }}" class="inline">
                                @csrf
                                <x-button variant="secondary" type="submit">
                                    {{ $gate->activeToken ? 'Regenerasi QR' : 'Terbitkan QR' }}
                                </x-button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-3 text-center text-zinc-500" colspan="5">Belum ada gerbang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>