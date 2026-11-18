<x-layouts.tenant :title="'Daftar Reservasi — ' . $tenant->display_name" :tenant="$tenant">
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Reservasi Pengunjung</h2>
            <p class="text-sm text-slate-500">Kelola dan pantau seluruh transaksi pemesanan tiket wisatawan yang berkaitan dengan destinasi Anda.</p>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <form method="GET" action="{{ route('tenant.reservasi', ['tenant' => $tenant->code]) }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Search --}}
                    <div>
                        <label for="search" class="block text-xs font-semibold text-slate-600 mb-1">Cari Kode / Pemesan</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Kode booking, nama, email..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                    </div>

                    {{-- Tanggal Kunjungan --}}
                    <div>
                        <label for="visit_date" class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Kunjungan</label>
                        <input type="date" id="visit_date" name="visit_date" value="{{ $visitDate }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                    </div>

                    {{-- Status Pembayaran --}}
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-600 mb-1">Status Pembayaran</label>
                        <select id="status" name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                            <option value="">Semua Status</option>
                            <option value="paid" @selected($paymentStatus === 'paid')>Lunas (Paid)</option>
                            <option value="completed" @selected($paymentStatus === 'completed')>Selesai (Completed)</option>
                            <option value="pending_payment" @selected($paymentStatus === 'pending_payment')>Menunggu Pembayaran</option>
                            <option value="cancelled" @selected($paymentStatus === 'cancelled')>Dibatalkan</option>
                            <option value="expired" @selected($paymentStatus === 'expired')>Kedaluwarsa</option>
                        </select>
                    </div>

                    {{-- Status Penggunaan Tiket --}}
                    <div>
                        <label for="ticket_status" class="block text-xs font-semibold text-slate-600 mb-1">Status Penggunaan Tiket</label>
                        <select id="ticket_status" name="ticket_status" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                            <option value="">Semua</option>
                            <option value="used" @selected($ticketStatus === 'used')>Sudah Dipakai (Check-in)</option>
                            <option value="unused" @selected($ticketStatus === 'unused')>Belum Dipakai</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('tenant.reservasi', ['tenant' => $tenant->code]) }}" 
                       class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Reset Filter
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-blue-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel Reservasi --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Daftar Transaksi Reservasi</h3>
                <span class="text-xs text-slate-500">Ditemukan {{ $bookings->total() }} reservasi</span>
            </div>

            @if ($bookings->isEmpty())
                <div class="p-8">
                    <x-empty-state title="Tidak Ada Reservasi" message="Tidak ditemukan data reservasi yang sesuai dengan kriteria pencarian." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/75 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-3.5 px-4">Kode Booking</th>
                                <th class="py-3.5 px-4">Pemesan</th>
                                <th class="py-3.5 px-4">Tanggal Kunjungan</th>
                                <th class="py-3.5 px-4">Item Tiket</th>
                                <th class="py-3.5 px-4 text-right">Subtotal Tenant</th>
                                <th class="py-3.5 px-4 text-center">Status Bayar</th>
                                <th class="py-3.5 px-4 text-center">Tiket Check-in</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($bookings as $booking)
                                @php
                                    $tenantSubtotal = $booking->items->sum('subtotal');
                                    $totalTenantTickets = $booking->tickets->count();
                                    $usedTenantTickets = $booking->tickets->where('status', 'checked_in')->count();
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 px-4">
                                        <a href="{{ route('tenant.reservasi.show', ['tenant' => $tenant->code, 'booking' => $booking->booking_code]) }}" 
                                           class="font-mono font-bold text-xs text-blue-600 hover:underline">
                                            {{ $booking->booking_code }}
                                        </a>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $booking->created_at->format('d/m/Y H:i') }} WIB
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $booking->customer_name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $booking->customer_email }}</div>
                                        @if ($booking->customer_phone)
                                            <div class="text-[11px] text-slate-400">WA: {{ $booking->customer_phone }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-semibold text-slate-900">
                                            {{ $booking->visit_date ? $booking->visit_date->format('d M Y') : '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-0.5">
                                            @foreach ($booking->items as $item)
                                                <div class="text-xs text-slate-700">
                                                    <span class="font-bold">{{ $item->quantity }}x</span> {{ $item->ticketType->name ?? 'Tiket' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        Rp {{ number_format($tenantSubtotal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold 
                                            {{ $booking->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 
                                              ($booking->status === 'completed' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                                              ($booking->status === 'pending_payment' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600')) }}">
                                            {{ $booking->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($totalTenantTickets > 0)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $usedTenantTickets == $totalTenantTickets ? 'text-emerald-600' : ($usedTenantTickets > 0 ? 'text-blue-600' : 'text-slate-500') }}">
                                                {{ $usedTenantTickets }}/{{ $totalTenantTickets }}
                                                @if ($usedTenantTickets == $totalTenantTickets)
                                                    <span class="text-emerald-500">✓</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('tenant.reservasi.show', ['tenant' => $tenant->code, 'booking' => $booking->booking_code]) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                                            <span>Rincian</span>
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.tenant>
