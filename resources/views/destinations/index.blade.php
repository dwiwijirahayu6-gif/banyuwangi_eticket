@extends('layouts.app')

@section('title', 'Informasi Tiket')

@section('content')
<section class="max-w-5xl mx-auto px-6 py-10 space-y-8">
    <div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Informasi Tiket</h1>
        <p class="text-sm text-slate-500 mt-1">Harga tiket, jam operasional, dan ketentuan pemesanan destinasi wisata Banyuwangi.</p>
    </div>

    <div class="space-y-5">
        @forelse ($destinations as $destination)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden md:flex">
                <img src="{{ asset($destination->image) }}" alt="{{ $destination->name }}" class="w-full md:w-64 h-44 md:h-auto object-cover">
                <div class="p-5 flex-1 space-y-3">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $destination->name }}</h2>
                            <p class="text-xs text-slate-500">Pengelola: {{ $destination->tenant->name ?? 'Dinas Pariwisata' }}</p>
                        </div>
                        <span class="text-[11px] bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full">Buka {{ $destination->operating_hours }}</span>
                    </div>

                    <p class="text-sm text-slate-600">{{ $destination->description }}</p>

                    <ul class="divide-y divide-slate-100 border border-slate-100 rounded-xl">
                        @forelse ($destination->ticketTypes as $type)
                            <li class="flex justify-between px-4 py-2 text-sm">
                                <span>{{ $type->name }}</span>
                                <span class="font-semibold text-slate-900">Rp {{ number_format((float) $type->price, 0, ',', '.') }}</span>
                            </li>
                        @empty
                            <li class="px-4 py-2 text-sm text-slate-400">Belum ada jenis tiket.</li>
                        @endforelse
                    </ul>

                    <a href="{{ route('destinations.show', $destination) }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-full transition">Pesan Tiket</a>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-500 text-sm bg-white rounded-2xl border border-slate-200">Belum ada data destinasi.</div>
        @endforelse
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
        <h2 class="text-lg font-bold text-slate-900">Ketentuan Pemesanan</h2>
        <ul class="text-sm text-slate-600 list-disc list-inside space-y-1.5">
            <li>Maksimal {{ $maxTickets }} tiket dalam satu pesanan, untuk tanggal kunjungan hingga 30 hari ke depan.</li>
            <li>Pembayaran melalui QRIS dan harus diselesaikan dalam {{ $paymentWindow }} menit. Setelah itu pesanan otomatis kedaluwarsa.</li>
            <li>E-tiket (kode QR) terbit setelah pembayaran berhasil dan ditunjukkan kepada petugas di gerbang.</li>
            <li>Pesanan yang belum dibayar dapat dibatalkan kapan saja.</li>
            <li>Pesanan yang sudah dibayar dapat dibatalkan paling lambat H-1 sebelum tanggal kunjungan, selama tiket belum digunakan. Pengembalian dana diproses oleh pengelola destinasi.</li>
        </ul>
        <a href="{{ route('bookings.lookup') }}" class="inline-block text-xs font-semibold text-blue-600 hover:text-blue-800">Cek atau batalkan pesanan &rarr;</a>
    </div>
</section>
@endsection