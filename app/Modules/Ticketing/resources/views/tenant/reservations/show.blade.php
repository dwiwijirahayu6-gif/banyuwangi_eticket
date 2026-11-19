<x-layouts.tenant :title="'Detail Reservasi #' . $booking->booking_code" :tenant="$tenant">
    <div class="max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('tenant.reservasi', ['tenant' => $tenant->code]) }}" 
                   class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Daftar Reservasi</span>
                </a>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Rincian Reservasi Pengunjung</h2>
            </div>

            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold 
                {{ $booking->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 
                  ($booking->status === 'completed' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                  ($booking->status === 'pending_payment' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700')) }}">
                Status Pembayaran: {{ $booking->statusLabel() }}
            </span>
        </div>

        {{-- Kartu Data Pemesan & Informasi Pesanan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Data Wisatawan / Pemesan --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Data Pemesan (Wisatawan)</h3>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nama Lengkap</span>
                        <span class="font-bold text-slate-900">{{ $booking->customer_name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Surel (Email)</span>
                        <span class="font-medium text-slate-900">{{ $booking->customer_email }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nomor WhatsApp / HP</span>
                        <span class="font-medium text-slate-900">{{ $booking->customer_phone ?: '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Metadata Transaksi --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Transaksi</h3>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Kode Booking</span>
                        <span class="font-mono font-bold text-blue-600">{{ $booking->booking_code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Waktu Pemesanan</span>
                        <span class="font-medium text-slate-900">{{ $booking->created_at->format('d/m/Y H:i') }} WIB</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Tanggal Kunjungan</span>
                        <span class="font-bold text-slate-900">{{ $booking->visit_date ? $booking->visit_date->translatedFormat('d F Y') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Item Tiket Milik Tenant Ini --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Item Tiket Destinasi Anda</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="pb-2">Jenis Tiket</th>
                            <th class="pb-2 text-right">Harga Satuan (Snapshot)</th>
                            <th class="pb-2 text-center">Jumlah</th>
                            <th class="pb-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($booking->items as $item)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900">
                                    {{ $item->ticketType->name ?? 'Tiket Masuk' }}
                                    <div class="text-[11px] text-slate-400 font-normal">Kategori: {{ $item->ticketType->category ?? '-' }}</div>
                                </td>
                                <td class="py-3 text-right text-slate-600">
                                    Rp {{ number_format($item->unit_price_snapshot, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-center font-bold text-slate-900">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-3 text-right font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200">
                            <td colspan="3" class="pt-4 font-bold text-slate-900 text-right">Total Hak Tagihan Tenant:</td>
                            <td class="pt-4 font-extrabold text-blue-600 text-lg text-right">
                                Rp {{ number_format($tenantSubtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Lembar Tiket Digital & Status Penggunaan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Lembar Tiket Digital ({{ $booking->tickets->count() }} Lembar)</h3>
                <span class="text-xs text-slate-500">Tiket hanya dapat divalidasi oleh petugas destinasi Anda</span>
            </div>

            @if ($booking->tickets->isEmpty())
                <p class="text-xs text-slate-500 py-3">Tiket digital belum terbit (menunggu pembayaran lunas).</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($booking->tickets as $ticket)
                        <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50 flex items-center justify-between gap-4">
                            <div>
                                <span class="font-mono font-bold text-xs text-slate-900">{{ $ticket->ticket_code }}</span>
                                <p class="text-xs text-slate-600 mt-0.5">{{ $ticket->bookingItem->ticketType->name ?? 'Tiket' }}</p>
                                <p class="text-[11px] text-slate-400">Tanggal Kunjungan: {{ $ticket->visit_date ? $ticket->visit_date->format('d/m/Y') : '-' }}</p>
                            </div>

                            <div class="text-right">
                                @if ($ticket->status === 'checked_in')
                                    <span class="inline-flex rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                        ✓ Sudah Masuk
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        {{ $ticket->updated_at ? $ticket->updated_at->format('d/m H:i') : '' }}
                                        @if ($ticket->verifiedBy)
                                            <br>oleh {{ $ticket->verifiedBy->name }}
                                        @endif
                                    </p>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                        Belum Dipakai
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.tenant>
