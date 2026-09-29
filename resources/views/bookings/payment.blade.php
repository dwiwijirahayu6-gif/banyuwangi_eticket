@extends('layouts.app')

@section('title', 'Pembayaran '.$booking->booking_code)

@section('content')
<section class="max-w-2xl mx-auto px-6 py-8 space-y-5">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Pembayaran</h1>
        <p class="text-sm text-slate-500">Kode pesanan <span class="font-mono font-semibold text-slate-700">{{ $booking->booking_code }}</span></p>
    </div>

    @if ($booking->status === 'pending_payment')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-center space-y-4">
            <p class="text-xs text-slate-500">Selesaikan pembayaran dalam</p>
            <p id="countdown" class="text-3xl font-extrabold text-blue-600" data-expires="{{ $booking->expires_at->toIso8601String() }}">--:--</p>

            <div class="flex justify-center"><div id="qris" class="p-3 bg-white border border-slate-200 rounded-xl" data-payload="{{ $booking->payment->qris_payload }}"></div></div>

            <div>
                <p class="text-xs text-slate-500">Total pembayaran</p>
                <p class="text-2xl font-extrabold text-slate-900">Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Ref: {{ $booking->payment->payment_reference }}</p>
            </div>

            <p class="text-xs text-slate-500">Scan kode QRIS di atas menggunakan aplikasi e-wallet atau mobile banking Anda.</p>

            @if ($canSimulate)
                <form method="POST" action="{{ route('payment.simulate', $booking) }}" class="pt-2 border-t border-dashed border-slate-200">
                    @csrf
                    <p class="text-[11px] text-amber-600 mb-2">Mode pengembangan: pembayaran disimulasikan, belum terhubung ke payment gateway.</p>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">Simulasikan Pembayaran Berhasil</button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm space-y-2">
            <h2 class="font-bold text-slate-900">Ringkasan Pesanan</h2>
            <p class="text-slate-500">Kunjungan: {{ $booking->visit_date->locale('id')->translatedFormat('l, d F Y') }}</p>
            @foreach ($booking->items as $item)
                <div class="flex justify-between">
                    <span>{{ $item->ticketType->destination->name }} &ndash; {{ $item->ticketType->name }} &times; {{ $item->quantity }}</span>
                    <span class="font-medium">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan pesanan ini?')" class="text-center">
            @csrf
            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Batalkan pesanan</button>
        </form>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center space-y-3">
            <p class="text-sm text-slate-600">Status pesanan: <strong>{{ $booking->statusLabel() }}</strong>.</p>
            @if ($booking->status === 'expired')
                <p class="text-sm text-slate-500">Batas waktu pembayaran telah habis. Silakan buat pesanan baru.</p>
            @endif
            <a href="{{ route('home') }}#katalog" class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-full transition">Pilih Destinasi</a>
        </div>
    @endif
</section>
@endsection

@if ($booking->status === 'pending_payment')
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    (function () {
        const qris = document.getElementById('qris');
        new QRCode(qris, { text: qris.dataset.payload, width: 200, height: 200 });

        const el = document.getElementById('countdown');
        const expires = new Date(el.dataset.expires).getTime();
        function tick() {
            const left = Math.floor((expires - Date.now()) / 1000);
            if (left <= 0) { window.location.reload(); return; }
            const m = String(Math.floor(left / 60)).padStart(2, '0');
            const s = String(left % 60).padStart(2, '0');
            el.textContent = m + ':' + s;
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush
@endif