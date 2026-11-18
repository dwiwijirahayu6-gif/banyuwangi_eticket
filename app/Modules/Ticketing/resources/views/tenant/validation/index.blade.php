<x-layouts.tenant :title="'Validasi Tiket Digital — ' . $tenant->display_name" :tenant="$tenant">
    <div class="max-w-4xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Validasi Tiket Masuk (Check-in Manual)</h2>
            <p class="text-sm text-slate-500">Masukkan kode booking alfanumerik atau kode tiket yang ditunjukkan oleh wisatawan untuk verifikasi pintu masuk.</p>
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

        {{-- Form Pencarian Manual (Alfanumerik) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 md:p-8 shadow-xs">
            <form method="GET" action="{{ route('tenant.validasi-tiket', ['tenant' => $tenant->code]) }}" class="space-y-4">
                <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Masukkan Kode Alfanumerik (Kode Booking / Tiket)
                </label>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full">
                        <input type="text" id="code" name="code" value="{{ $code }}" autofocus required 
                               placeholder="Contoh: BWI-20260918-001 atau TIX-BWI-20260918-001"
                               class="w-full uppercase font-mono tracking-wider rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-base font-bold text-slate-900 placeholder:normal-case placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">
                    </div>
                    <button type="submit" 
                            class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Cari &amp; Periksa Tiket</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400">Verifikasi dilakukan secara manual menggunakan kode alfanumerik unik sesuai standar SRS tanpa alat pemindai kamera.</p>
            </form>
        </div>

        {{-- Hasil Pencarian Tiket --}}
        @if ($code !== '')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">
                        Hasil Pencarian untuk: <span class="font-mono text-blue-600 font-bold">{{ $code }}</span>
                    </h3>
                    <span class="text-xs text-slate-500">{{ $tickets->count() }} lembar tiket ditemukan</span>
                </div>

                @if ($tickets->isEmpty())
                    <div class="p-6 text-center">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600 mb-3">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Tiket Tidak Valid / Tidak Ditemukan</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                            Kode alfanumerik tidak ditemukan di sistem atau bukan merupakan tiket untuk destinasi {{ $tenant->display_name }}. Pastikan wisatawan menunjukkan kode pemesanan yang benar.
                        </p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($tickets as $ticket)
                            @php
                                $isPaid = $ticket->booking->status === 'paid' || $ticket->booking->status === 'completed';
                                $isRedeemed = $ticket->status === 'checked_in';
                                $canVerify = $isPaid && !$isRedeemed;
                            @endphp
                            <div class="rounded-xl border {{ $isRedeemed ? 'border-emerald-200 bg-emerald-50/30' : ($canVerify ? 'border-blue-200 bg-blue-50/20' : 'border-red-200 bg-red-50/20') }} p-5 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/60 pb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-extrabold text-base text-slate-900">{{ $ticket->ticket_code }}</span>
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isPaid ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $ticket->booking->statusLabel() }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">Kode Booking Induk: <span class="font-mono font-semibold">{{ $ticket->booking->booking_code }}</span></p>
                                    </div>

                                    <div>
                                        @if ($isRedeemed)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                                ✓ Sudah Digunakan (Check-in)
                                            </span>
                                        @elseif ($canVerify)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800">
                                                ● Sah &amp; Siap Masuk
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800">
                                                ✕ Belum Lunas
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Rincian Pengunjung & Tiket --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                    <div class="rounded-lg bg-white p-3 border border-slate-100">
                                        <span class="text-slate-400 uppercase tracking-wider block font-semibold text-[10px]">Nama Pemesan</span>
                                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $ticket->booking->customer_name }}</span>
                                        <span class="text-slate-500">{{ $ticket->booking->customer_phone ?: $ticket->booking->customer_email }}</span>
                                    </div>
                                    <div class="rounded-lg bg-white p-3 border border-slate-100">
                                        <span class="text-slate-400 uppercase tracking-wider block font-semibold text-[10px]">Jenis Tiket</span>
                                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $ticket->bookingItem->ticketType->name ?? 'Tiket Masuk' }}</span>
                                        <span class="text-slate-500">Kategori: {{ $ticket->bookingItem->ticketType->category ?? '-' }}</span>
                                    </div>
                                    <div class="rounded-lg bg-white p-3 border border-slate-100">
                                        <span class="text-slate-400 uppercase tracking-wider block font-semibold text-[10px]">Tanggal Kunjungan</span>
                                        <span class="font-bold text-slate-900 text-sm mt-0.5 block">
                                            {{ $ticket->visit_date ? $ticket->visit_date->translatedFormat('d F Y') : '-' }}
                                        </span>
                                        <span class="text-slate-500">Harga: Rp {{ number_format($ticket->bookingItem->unit_price_snapshot ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                {{-- Aksi Verifikasi --}}
                                <div class="flex items-center justify-between pt-2">
                                    @if ($isRedeemed)
                                        <p class="text-xs text-slate-500">
                                            Diverifikasi pada <span class="font-semibold text-slate-700">{{ $ticket->updated_at ? $ticket->updated_at->format('d/m/Y H:i') : '-' }} WIB</span>
                                            oleh <span class="font-semibold text-slate-700">{{ $ticket->verifiedBy?->name ?? 'Petugas' }}</span>.
                                        </p>
                                    @elseif ($canVerify)
                                        <form method="POST" action="{{ route('tenant.validasi-tiket.verify', ['tenant' => $tenant->code]) }}" class="ms-auto">
                                            @csrf
                                            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                            <input type="hidden" name="search_code" value="{{ $code }}">
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-600/20 transition">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Verifikasi Masuk</span>
                                            </button>
                                        </form>
                                    @else
                                        <p class="text-xs text-red-600 font-semibold">
                                            Tiket tidak dapat diverifikasi karena transaksi belum berstatus Lunas.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- 10 Validasi Terakhir Hari Ini --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Riwayat Verifikasi Tiket Terbaru</h3>

            @if ($recentValidations->isEmpty())
                <p class="text-xs text-slate-500 py-3">Belum ada tiket yang divalidasi hari ini.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($recentValidations as $ticket)
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div>
                                <span class="font-mono font-bold text-xs text-slate-900">{{ $ticket->ticket_code }}</span>
                                <span class="text-xs text-slate-600 ms-2">{{ $ticket->bookingItem->ticketType->name ?? 'Tiket' }}</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">Pemesan: {{ $ticket->booking->customer_name ?? '-' }} (Booking: {{ $ticket->booking->booking_code }})</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-0.5">
                                    ✓ Terverifikasi Masuk
                                </span>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $ticket->updated_at ? $ticket->updated_at->format('H:i') : '' }} WIB • {{ $ticket->verifiedBy?->name ?? 'Petugas' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.tenant>
