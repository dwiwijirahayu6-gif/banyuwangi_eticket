@extends('layouts.app')

@section('title', $destination->name)

@section('content')
<section class="max-w-5xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        {{-- Informasi destinasi --}}
        <div class="lg:col-span-2 space-y-4">
            <img src="{{ asset($destination->image) }}" alt="{{ $destination->name }}" class="w-full h-56 object-cover rounded-2xl shadow-sm">
            <h1 class="text-2xl font-extrabold text-slate-900">{{ $destination->name }}</h1>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $destination->description }}</p>
            <dl class="text-sm bg-white border border-slate-200 rounded-xl divide-y divide-slate-100">
                <div class="flex justify-between px-4 py-2"><dt class="text-slate-500">Jam operasional</dt><dd class="font-medium">{{ $destination->operating_hours }}</dd></div>
                <div class="flex justify-between px-4 py-2"><dt class="text-slate-500">Pengelola</dt><dd class="font-medium text-right">{{ $destination->tenant->name ?? 'Dinas Pariwisata' }}</dd></div>
                <div class="flex justify-between px-4 py-2"><dt class="text-slate-500">Batas bayar</dt><dd class="font-medium">{{ $paymentWindow }} menit</dd></div>
            </dl>
            <a href="{{ route('tickets.info') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Lihat ketentuan tiket &rarr;</a>
        </div>

        {{-- Form checkout --}}
        <form method="POST" action="{{ route('checkout.store', $destination) }}" id="checkout-form"
              class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            @csrf
            <h2 class="text-lg font-bold text-slate-900">Pesan Tiket</h2>

            <div class="space-y-1">
                <label for="visit_date" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal Kunjungan</label>
                <input type="date" id="visit_date" name="visit_date" required
                       min="{{ $minDate }}" max="{{ $maxDate }}" value="{{ old('visit_date', $minDate) }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
            </div>

            <div class="space-y-2">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Jenis &amp; Jumlah Tiket (maks. {{ $maxTickets }})</span>
                @forelse ($destination->ticketTypes as $type)
                    <div class="flex items-center justify-between gap-4 border border-slate-200 rounded-xl px-4 py-3">
                        <div>
                            <p class="text-sm font-medium">{{ $type->name }}</p>
                            <p class="text-xs text-slate-500">Rp {{ number_format((float) $type->price, 0, ',', '.') }} / tiket</p>
                        </div>
                        <input type="number" name="quantities[{{ $type->id }}]" min="0" max="{{ $maxTickets }}"
                               value="{{ old('quantities.'.$type->id, 0) }}" data-price="{{ (int) round((float) $type->price) }}"
                               class="qty w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:border-blue-500">
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tiket untuk destinasi ini belum tersedia.</p>
                @endforelse
            </div>

            <div class="space-y-4">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Data Pemesan</span>
                <input type="text" name="customer_name" placeholder="Nama lengkap" value="{{ old('customer_name') }}" required maxlength="100"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                <input type="email" name="customer_email" placeholder="Email (untuk cek &amp; batalkan pesanan)" value="{{ old('customer_email') }}" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                <input type="tel" name="customer_phone" placeholder="No. HP, contoh 081234567890" value="{{ old('customer_phone') }}" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                <div>
                    <p class="text-xs text-slate-500">Total pembayaran</p>
                    <p class="text-xl font-extrabold text-slate-900" id="total">Rp 0</p>
                </div>
                <button type="submit" @disabled($destination->ticketTypes->isEmpty())
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-md transition">
                    Lanjut ke Pembayaran
                </button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        const inputs = document.querySelectorAll('.qty');
        const totalEl = document.getElementById('total');
        function update() {
            let total = 0;
            inputs.forEach(function (input) {
                total += (parseInt(input.value, 10) || 0) * parseInt(input.dataset.price, 10);
            });
            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        inputs.forEach(function (input) { input.addEventListener('input', update); });
        update();
    })();
</script>
@endpush