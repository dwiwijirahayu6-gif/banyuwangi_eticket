@php
if (!isset($tenant) || !($tenant instanceof \App\Models\Tenant)) {
$tenant = \App\Models\Tenant::where('code', (string)($tenant ?? ''))->first()
?? new \App\Models\Tenant([
'name' => (string)($tenant ?? 'Tenant'),
'code' => (string)($tenant ?? 'demo')
]);
}
@endphp

<x-layouts.tenant :title="'Dashboard — ' . ($destination->name ?? $tenant->display_name)" :tenant="$tenant">
    <div class="space-y-6">

        {{-- Banner Destinasi Wisata --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-900 shadow-md">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">

                <div class="flex items-center gap-5">
                    @if ($destination && $destination->image)
                    <img
                        src="{{ asset($destination->image) }}"
                        alt="{{ $destination->name }}"
                        class="h-20 w-20 rounded-xl object-cover ring-2 ring-white shadow-sm">
                    @else
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700 font-bold text-2xl border border-blue-200">
                        AL
                    </div>
                    @endif

                    <div>
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 mb-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Destinasi Operasional
                        </div>

                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">
                            {{ $destination->name ?? $tenant->display_name }}
                        </h2>

                        <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-medium text-slate-600">
                            <span>
                                📍 {{ $destination->location ?? 'Banyuwangi, Jawa Timur' }}
                            </span>

                            <span>
                                🕒 {{ $destination->operating_hours ?? '08:00 - 16:00 WIB' }}
                            </span>

                            <span>
                                🏷️ Kode:
                                <span class="font-mono font-semibold text-slate-800">
                                    {{ $tenant->code }}
                                </span>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('tenant.validasi-tiket', ['tenant' => $tenant->code]) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Validasi Tiket</span>
                    </a>

                    <a
                        href="{{ route('tenant.destinasi', ['tenant' => $tenant->code]) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-xs font-bold text-blue-700 hover:bg-blue-50 transition">
                        <span>Kelola Profil</span>
                    </a>
                </div>

            </div>
        </div>

        {{-- Statistik Utama (8 KPI Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Tiket Terjual --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tiket Terjual</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ number_format($totalTicketsSold) }}</p>
                <p class="mt-1 text-xs text-slate-500">Tiket dari pesanan lunas</p>
            </div>

            {{-- Pengunjung Check-in --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pengunjung</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ number_format($totalVisitors) }}</p>
                <p class="mt-1 text-xs text-emerald-600 font-medium">Tiket terverifikasi masuk</p>
            </div>

            {{-- Total Reservasi --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Reservasi</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ number_format($totalReservations) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $paidReservations }} lunas, {{ $pendingReservations }} pending</p>
            </div>

            {{-- Menunggu Pembayaran --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Bayar</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-amber-600">{{ number_format($pendingReservations) }}</p>
                <p class="mt-1 text-xs text-slate-500">Dalam batas waktu bayar</p>
            </div>

            {{-- Reservasi Lunas --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Reservasi Lunas</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ number_format($paidReservations) }}</p>
                <p class="mt-1 text-xs text-teal-600 font-medium">Pembayaran terverifikasi</p>
            </div>

            {{-- Tiket Sudah Digunakan --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tiket Terpakai</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-slate-900">{{ number_format($usedTicketsCount) }}</p>
                <p class="mt-1 text-xs text-slate-500">Dari total {{ number_format($totalTicketsSold) }} tiket terjual</p>
            </div>

            {{-- Pendapatan Bersih Tenant --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendapatan Bersih</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xl font-extrabold text-slate-900">Rp {{ number_format($netRevenue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">Pasca potongan komisi</p>
            </div>

            {{-- Saldo Tersedia Ditarik --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Saldo Siap Ditarik</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xl font-extrabold text-emerald-700">Rp {{ number_format($availableBalance, 0, ',', '.') }}</p>
                <a
                    href="{{ route('tenant.saldo-penarikan', ['tenant' => $tenant->code]) }}"
                    class="mt-1 inline-block text-xs font-semibold text-emerald-700 hover:underline">
                    Ajukan Penarikan &rarr;
                </a>
            </div>

        </div>

        {{-- Visualisasi Tren Penjualan & Pengunjung 7 Hari Terakhir --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Statistik Operasional 7 Hari Terakhir</h3>
                    <p class="text-xs text-slate-500">Tren penjualan tiket dan kedatangan pengunjung harian</p>
                </div>

                <a
                    href="{{ route('tenant.laporan', ['tenant' => $tenant->code]) }}"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <span>Lihat Laporan Lengkap</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th class="pb-3">Tanggal</th>
                            <th class="pb-3 text-center">Tiket Terjual</th>
                            <th class="pb-3 text-center">Pengunjung Hadir</th>
                            <th class="pb-3 text-right">Pendapatan Kotor</th>
                            <th class="pb-3 text-right">Visualisasi Volume</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 font-medium">
                        @php
                        $maxTickets = max(array_column($chartData, 'tickets') ?: [1]);
                        if ($maxTickets == 0) {
                        $maxTickets = 1;
                        }
                        @endphp

                        @foreach ($chartData as $row)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 text-slate-900 font-semibold">{{ $row['date'] }}</td>
                            <td class="py-3 text-center">{{ $row['tickets'] }} tiket</td>
                            <td class="py-3 text-center text-emerald-600 font-semibold">{{ $row['visitors'] }} orang</td>
                            <td class="py-3 text-right font-semibold text-slate-900">
                                Rp {{ number_format($row['gross'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 text-right w-44">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-32 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div
                                            class="bg-blue-600 h-2 rounded-full"
                                            @style(['width'=> min(100, round(($row['tickets'] / $maxTickets) * 100)) . '%'])
                                            ></div>
                                    </div>
                                    <span class="text-[11px] text-slate-400 w-8 text-right">{{ $row['tickets'] }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ringkasan Aktivitas Terbaru (2 Kolom) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 5 Reservasi Terbaru --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Reservasi Terbaru</h3>
                    <a
                        href="{{ route('tenant.reservasi', ['tenant' => $tenant->code]) }}"
                        class="text-xs font-semibold text-blue-600 hover:underline">
                        Semua
                    </a>
                </div>

                @if ($recentBookings->isEmpty())
                <x-empty-state
                    title="Belum Ada Reservasi"
                    message="Reservasi dari wisatawan akan otomatis muncul di sini." />
                @else
                <div class="divide-y divide-slate-100">
                    @foreach ($recentBookings as $booking)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div>
                            <a
                                href="{{ route('tenant.reservasi.show', ['tenant' => $tenant->code, 'booking' => $booking->booking_code]) }}"
                                class="text-xs font-bold font-mono text-blue-600 hover:underline">
                                {{ $booking->booking_code }}
                            </a>

                            <p class="text-xs font-medium text-slate-900 mt-0.5">
                                {{ $booking->customer_name }}
                            </p>

                            <p class="text-[11px] text-slate-500">
                                Kunjungan: {{ $booking->visit_date ? $booking->visit_date->format('d/m/Y') : '-' }}
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold
                                        {{ $booking->status === 'paid'
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                            : ($booking->status === 'pending_payment'
                                                ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                                : 'bg-slate-100 text-slate-700') }}">
                                {{ $booking->statusLabel() }}
                            </span>

                            <p class="text-xs font-semibold text-slate-900 mt-1">
                                Rp {{ number_format($booking->items->sum('subtotal'), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- 5 Validasi / Check-in Tiket Terakhir --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Validasi Masuk Terakhir</h3>
                    <a
                        href="{{ route('tenant.validasi-tiket', ['tenant' => $tenant->code]) }}"
                        class="text-xs font-semibold text-blue-600 hover:underline">
                        Scan/Cari Tiket
                    </a>
                </div>

                @if ($recentValidations->isEmpty())
                <x-empty-state
                    title="Belum Ada Tiket Divalidasi"
                    message="Gunakan menu Validasi Tiket untuk memverifikasi tiket wisatawan di pintu masuk." />
                @else
                <div class="divide-y divide-slate-100">
                    @foreach ($recentValidations as $ticket)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold font-mono text-slate-900">
                                {{ $ticket->ticket_code }}
                            </span>

                            <p class="text-xs text-slate-600 mt-0.5">
                                {{ $ticket->bookingItem->ticketType->name ?? 'Tiket Masuk' }}
                            </p>

                            <p class="text-[11px] text-slate-400">
                                Pemesan: {{ $ticket->booking->customer_name ?? '-' }}
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                ✓ Masuk
                            </span>

                            <p class="text-[11px] text-slate-500 mt-1">
                                {{ $ticket->updated_at ? $ticket->updated_at->format('H:i') : '' }} WIB
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>
</x-layouts.tenant>