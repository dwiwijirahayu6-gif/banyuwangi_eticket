@extends('layouts.app')

@section('title', 'Cek & Pembatalan Tiket')

@section('content')
<section class="max-w-md mx-auto px-6 py-10 space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Cek &amp; Pembatalan Tiket</h1>
        <p class="text-sm text-slate-500 mt-1">Masukkan kode pesanan dan email yang Anda gunakan saat memesan.</p>
    </div>

    <form method="POST" action="{{ route('bookings.lookup.submit') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        @csrf
        <div class="space-y-1">
            <label for="booking_code" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Kode Pesanan</label>
            <input type="text" id="booking_code" name="booking_code" value="{{ old('booking_code') }}" placeholder="TIX-BWI-20260929-XXXXXX" required
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:outline-none focus:border-blue-500">
        </div>
        <div class="space-y-1">
            <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-md transition">Cari Pesanan</button>
    </form>

    <p class="text-xs text-slate-500 bg-white border border-slate-200 rounded-xl px-4 py-3">{{ $cutoffNote }} Pesanan yang belum dibayar dapat dibatalkan kapan saja.</p>
</section>
@endsection