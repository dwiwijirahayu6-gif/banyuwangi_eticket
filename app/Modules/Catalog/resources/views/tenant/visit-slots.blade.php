<x-layouts.tenant :title="'Jadwal & Kuota Kunjungan — ' . ($destination->name ?? $tenant->display_name)" :tenant="$tenant">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Kuota dan Jadwal Kunjungan</h2>
                <p class="text-sm text-slate-500">Atur kapasitas kuota harian pengunjung dan kelola ketersediaan tanggal operasional destinasi Anda.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Form Tambah / Set Kuota Baru --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Buka / Tetapkan Kuota Tanggal Kunjungan</span>
            </h3>

            <form method="POST" action="{{ route('tenant.jadwal-kuota.store', ['tenant' => $tenant->code]) }}" 
                  class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                @csrf
                <div>
                    <label for="visit_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Kunjungan <span class="text-red-500">*</span></label>
                    <input type="date" id="visit_date" name="visit_date" required min="{{ date('Y-m-d') }}" value="{{ old('visit_date', date('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">
                    @error('visit_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="quota_capacity" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Batas Kuota Pengunjung <span class="text-red-500">*</span></label>
                    <input type="number" id="quota_capacity" name="quota_capacity" required min="1" max="50000" value="{{ old('quota_capacity', 500) }}" placeholder="Contoh: 500"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">
                    @error('quota_capacity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Jadwal Kuota</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Jadwal & Kuota --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Daftar Jadwal dan Kuota Harian</h3>
                <span class="text-xs text-slate-500">Total {{ $slots->total() }} jadwal</span>
            </div>

            @if ($slots->isEmpty())
                <div class="p-8">
                    <x-empty-state title="Belum Ada Jadwal Kuota" message="Tetapkan kuota tanggal kunjungan menggunakan formulir di atas." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-3.5 px-4">Tanggal Kunjungan</th>
                                <th class="py-3.5 px-4 text-center">Total Kuota</th>
                                <th class="py-3.5 px-4 text-center">Kuota Terpakai</th>
                                <th class="py-3.5 px-4 text-center">Sisa Kuota</th>
                                <th class="py-3.5 px-4 text-center">Status Jadwal</th>
                                <th class="py-3.5 px-4 text-right">Ubah Kuota / Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($slots as $slot)
                                @php
                                    $booked = max(0, $slot->quota_capacity - $slot->quota_remaining);
                                    $isClosed = !($slot->is_active ?? true);
                                    $percent = $slot->quota_capacity > 0 ? round(($booked / $slot->quota_capacity) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $slot->visit_date->translatedFormat('l, d F Y') }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $slot->visit_date->format('Y-m-d') }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                                        {{ number_format($slot->quota_capacity) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-blue-600">
                                        {{ number_format($booked) }}
                                        <div class="text-[10px] text-slate-400">{{ $percent }}% terpesan</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold {{ $slot->quota_remaining > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ number_format($slot->quota_remaining) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($isClosed)
                                            <span class="inline-flex rounded-full bg-red-50 border border-red-200 px-2.5 py-0.5 text-xs font-bold text-red-700">
                                                Ditutup Sementara
                                            </span>
                                        @elseif ($slot->quota_remaining <= 0)
                                            <span class="inline-flex rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                                                Kuota Penuh
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                                Buka
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Form Cepat Ubah Kapasitas Kuota --}}
                                            <form method="POST" action="{{ route('tenant.jadwal-kuota.update', ['tenant' => $tenant->code, 'visitSlot' => $slot->id]) }}" 
                                                  class="flex items-center gap-1.5"
                                                  onsubmit="return confirm('Perbarui kapasitas kuota tanggal {{ $slot->visit_date->format('d/m/Y') }}? Kapasitas tidak boleh kurang dari {{ $booked }}.');">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="quota_capacity" value="{{ $slot->quota_capacity }}" min="{{ $booked }}" max="50000"
                                                       title="Kapasitas minimal {{ $booked }} (jumlah tiket sudah dipesan)"
                                                       class="w-20 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs text-center font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600">
                                                <button type="submit" 
                                                        class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 transition"
                                                        title="Simpan kapasitas kuota">
                                                    Ubah
                                                </button>
                                            </form>

                                            {{-- Tombol Buka / Tutup Sementara --}}
                                            <form method="POST" action="{{ route('tenant.jadwal-kuota.toggle', ['tenant' => $tenant->code, 'visitSlot' => $slot->id]) }}">
                                                @csrf
                                                <button type="submit" 
                                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold transition {{ $isClosed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' }}">
                                                    {{ $isClosed ? 'Buka Kembali' : 'Tutup Sementara' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $slots->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.tenant>
