<x-layouts.tenant :title="'Laporan Penjualan — ' . ($destination->name ?? $tenant->display_name)" :tenant="$tenant">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Laporan Operasional &amp; Penjualan</h2>
                <p class="text-sm text-slate-500">Statistik pengunjung dan rincian mutasi pendapatan tiket destinasi Anda.</p>
            </div>

            {{-- Tombol Ekspor PDF & Excel --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('tenant.laporan.export.pdf', array_merge(['tenant' => $tenant->code], request()->query())) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition shadow-2xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ekspor PDF</span>
                </a>

                <a href="{{ route('tenant.laporan.export.excel', array_merge(['tenant' => $tenant->code], request()->query())) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition shadow-2xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ekspor Excel</span>
                </a>
            </div>
        </div>

        {{-- Filter Periode --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <form method="GET" action="{{ route('tenant.laporan', ['tenant' => $tenant->code]) }}" class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 mr-2">Pilih Periode:</span>
                    <button type="submit" name="period" value="today"
                            class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $period === 'today' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Hari Ini
                    </button>
                    <button type="submit" name="period" value="this_week"
                            class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $period === 'this_week' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Minggu Ini
                    </button>
                    <button type="submit" name="period" value="this_month"
                            class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $period === 'this_month' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Bulan Ini
                    </button>
                </div>

                {{-- Rentang Tanggal Kustom --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100 items-end">
                    <input type="hidden" name="period" value="custom">
                    <div>
                        <label for="from_date" class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                    </div>
                    <div>
                        <label for="to_date" class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" id="to_date" name="to_date" value="{{ $toDate }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                    </div>
                    <div>
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-xs transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span>Terapkan Rentang</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Indikator Periode Aktif --}}
        <div class="flex items-center justify-between text-xs text-slate-500 px-1">
            <span>Periode Laporan: <strong class="text-slate-900">{{ $report['period_label'] }}</strong></span>
            <span>Dihitung berdasarkan transaksi sebenarnya di database</span>
        </div>

        {{-- Ringkasan Metrik (8 Kotak) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider block">Tiket Terjual</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ number_format($report['total_tickets_sold']) }}</span>
                <span class="text-[10px] text-slate-500">Tiket lunas</span>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider block">Pengunjung Hadir</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">{{ number_format($report['total_visitors']) }}</span>
                <span class="text-[10px] text-emerald-600">Terverifikasi masuk</span>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider block">Total Booking</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ number_format($report['total_bookings']) }}</span>
                <span class="text-[10px] text-slate-500">{{ $report['total_paid_bookings'] }} lunas</span>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider block">Tiket Terpakai</span>
                <span class="text-2xl font-extrabold text-purple-600 mt-1 block">{{ number_format($report['total_used_tickets']) }}</span>
                <span class="text-[10px] text-purple-600">Checked-in</span>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider block">Pendapatan Kotor</span>
                <span class="text-lg font-extrabold text-slate-900 mt-1 block">Rp {{ number_format($report['gross_revenue'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-500">Omzet tiket</span>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider block">Komisi Platform</span>
                <span class="text-lg font-extrabold text-amber-600 mt-1 block">Rp {{ number_format($report['platform_commission'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-amber-600">Sesuai skema aktif</span>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider block">Pendapatan Bersih</span>
                <span class="text-lg font-extrabold text-blue-600 mt-1 block">Rp {{ number_format($report['net_revenue'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-blue-600">Hak tenant</span>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-xs">
                <span class="text-emerald-700 text-[11px] font-semibold uppercase tracking-wider block">Saldo Tersedia</span>
                <span class="text-lg font-extrabold text-emerald-700 mt-1 block">Rp {{ number_format($report['available_balance'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-emerald-600 font-medium">Buku besar (Ledger)</span>
            </div>
        </div>

        {{-- Tabel Ringkasan Transaksi Harian --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Rincian Transaksi Berdasarkan Tanggal</h3>
                <span class="text-xs text-slate-500">{{ $report['daily_summary']->count() }} hari tercatat</span>
            </div>

            @if ($report['daily_summary']->isEmpty())
                <div class="p-8">
                    <x-empty-state title="Belum Ada Transaksi" message="Tidak ada transaksi penjualan tiket pada periode yang dipilih." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-3.5 px-4">Tanggal</th>
                                <th class="py-3.5 px-4 text-center">Jumlah Booking</th>
                                <th class="py-3.5 px-4 text-center">Tiket Terjual</th>
                                <th class="py-3.5 px-4 text-center">Pengunjung Hadir</th>
                                <th class="py-3.5 px-4 text-right">Pendapatan Kotor</th>
                                <th class="py-3.5 px-4 text-right">Komisi Platform</th>
                                <th class="py-3.5 px-4 text-right">Pendapatan Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($report['daily_summary'] as $row)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 px-4 font-semibold text-slate-900">
                                        {{ \Carbon\Carbon::parse($row['date'])->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-medium">{{ $row['booking_count'] }}</td>
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-900">{{ $row['tickets_sold'] }}</td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-emerald-600">{{ $row['visitors'] }}</td>
                                    <td class="py-3.5 px-4 text-right font-medium text-slate-700">Rp {{ number_format($row['gross_revenue'], 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 text-right text-xs text-amber-600 font-medium">Rp {{ number_format($row['platform_commission'], 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 text-right font-bold text-blue-600">Rp {{ number_format($row['net_revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/90 font-bold border-t border-slate-200">
                            <tr>
                                <td class="py-3.5 px-4 text-slate-900">Total Keseluruhan</td>
                                <td class="py-3.5 px-4 text-center text-slate-900">{{ $report['daily_summary']->sum('booking_count') }}</td>
                                <td class="py-3.5 px-4 text-center text-slate-900">{{ $report['daily_summary']->sum('tickets_sold') }}</td>
                                <td class="py-3.5 px-4 text-center text-emerald-700">{{ $report['daily_summary']->sum('visitors') }}</td>
                                <td class="py-3.5 px-4 text-right text-slate-900">Rp {{ number_format($report['gross_revenue'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-4 text-right text-amber-700">Rp {{ number_format($report['platform_commission'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-4 text-right text-blue-700">Rp {{ number_format($report['net_revenue'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.tenant>
