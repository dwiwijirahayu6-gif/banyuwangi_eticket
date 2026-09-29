@extends('layouts.app')

@section('title', 'Pesanan '.$booking->booking_code)

@php
    $badge = [
        'pending_payment' => 'bg-amber-100 text-amber-800',
        'paid' => 'bg-emerald-100 text-emerald-800',
        'completed' => 'bg-slate-200 text-slate-700',
        'cancelled' => 'bg-red-100 text-red-800',
        'expired' => 'bg-slate-200 text-slate-600',
    ][$booking->status] ?? 'bg-slate-100 text-slate-700';
    $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
@endphp

@section('content')
<section class="max-w-3xl mx-auto px-6 py-8 space-y-5">
    <div class="flex flex-wrap justify-between items-start gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Detail Pesanan</h1>
            <p class="text-sm text-slate-500">Kode <span class="font-mono font-semibold text-slate-700">{{ $booking->booking_code }}</span></p>
        </div>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $badge }}">{{ $booking->statusLabel() }}</span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 text-sm space-y-3">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
            <dt class="text-slate-500">Pemesan</dt><dd class="font-medium text-right">{{ $booking->customer_name }}</dd>
            <dt class="text-slate-500">Email</dt><dd class="font-medium text-right break-all">{{ $booking->customer_email }}</dd>
            <dt class="text-slate-500">No. HP</dt><dd class="font-medium text-right">{{ $booking->customer_phone }}</dd>
            <dt class="text-slate-500">Tanggal kunjungan</dt>
            <dd class="font-medium text-right">{{ $booking->visit_date ? $booking->visit_date->locale('id')->translatedFormat('l, d F Y') : '-' }}</dd>
            @if ($booking->paid_at)
                <dt class="text-slate-500">Dibayar pada</dt><dd class="font-medium text-right">{{ $booking->paid_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB</dd>
            @endif
            @if ($booking->cancelled_at)
                <dt class="text-slate-500">Dibatalkan pada</dt><dd class="font-medium text-right">{{ $booking->cancelled_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB</dd>
                @if ($booking->cancel_reason)
                    <dt class="text-slate-500">Alasan</dt><dd class="font-medium text-right">{{ $booking->cancel_reason }}</dd>
                @endif
            @endif
        </dl>

        <div class="border-t border-slate-100 pt-3 space-y-1.5">
            @foreach ($booking->items as $item)
                <div class="flex justify-between">
                    <span>{{ $item->ticketType->destination->name }} &ndash; {{ $item->ticketType->name }} &times; {{ $item->quantity }}</span>
                    <span class="font-medium">{{ $rupiah($item->subtotal) }}</span>
                </div>
            @endforeach
            <div class="flex justify-between font-bold text-slate-900 pt-2 border-t border-slate-100">
                <span>Total</span><span>{{ $rupiah($booking->total_amount) }}</span>
            </div>
        </div>
    </div>

    @if ($booking->status === 'pending_payment')
        <a href="{{ route('payment.show', $booking) }}" class="block text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-md transition">Lanjutkan Pembayaran</a>
    @endif

    {{-- E-tiket --}}
    @if ($booking->tickets->isNotEmpty())
        <div class="space-y-3">
            <h2 class="text-lg font-bold text-slate-900">E-Tiket</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($booking->tickets as $ticket)
                    @php $active = $ticket->status === 'issued'; @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex gap-4 items-center {{ $active ? '' : 'opacity-60' }}">
                        <div class="ticket-qr shrink-0" data-code="{{ $ticket->ticket_code }}"></div>
                        <div class="text-xs space-y-1 min-w-0">
                            <p class="font-mono font-semibold text-slate-800 break-all">{{ $ticket->ticket_code }}</p>
                            <p class="text-slate-500">{{ $ticket->visit_date->locale('id')->translatedFormat('d F Y') }}</p>
                            <p class="font-semibold {{ $active ? 'text-emerald-600' : 'text-slate-500' }}">
                                {{ ['issued' => 'Aktif', 'checked_in' => 'Sudah digunakan', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan'][$ticket->status] ?? $ticket->status }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Pembatalan --}}
    @if (in_array($booking->status, ['pending_payment', 'paid']))
        <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
            <h2 class="text-lg font-bold text-slate-900">Batalkan Pesanan</h2>
            @if ($blockedReason)
                <p class="text-sm text-slate-600">{{ $blockedReason }}</p>
            @else
                @if ($booking->status === 'paid')
                    <p class="text-sm text-slate-600">Tiket akan dibatalkan. Pengembalian dana diproses oleh pengelola destinasi.</p>
                @endif
                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat diurungkan.')" class="space-y-3">
                    @csrf
                    <textarea name="cancel_reason" rows="2" maxlength="250" placeholder="Alasan pembatalan (opsional)"
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">{{ old('cancel_reason') }}</textarea>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">Batalkan Pesanan</button>
                </form>
            @endif
        </div>
    @endif

    <a href="{{ route('home') }}" class="inline-block text-xs font-semibold text-blue-600 hover:text-blue-800">&larr; Kembali ke beranda</a>
</section>
@endsection

@if ($booking->tickets->isNotEmpty())
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.querySelectorAll('.ticket-qr').forEach(function (el) {
        new QRCode(el, { text: el.dataset.code, width: 96, height: 96 });
    });
</script>
@endpush
@endif