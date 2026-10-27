<x-layouts.admin title="QR Gerbang {{ $gate->code }}">
    <h1 class="text-xl font-semibold print:hidden">QR — {{ $gate->label }} ({{ $gate->code }})</h1>

    @if ($url)
        <div class="mt-4 rounded-lg bg-amber-100 px-4 py-3 text-sm text-amber-900 print:hidden">
            QR baru ditampilkan <strong>satu kali</strong>. Unduh atau cetak sekarang.
        </div>
        
        <!-- Kotak QR Code -->
        <div class="mt-4 inline-flex flex-col items-center rounded-xl border border-zinc-200 bg-white p-6 text-zinc-900 print:border-none">
            <div class="text-sm font-bold uppercase tracking-widest">{{ config('app.name') }}</div>
            <div class="mt-1 text-2xl font-extrabold">{{ $gate->label }}</div>
            @if ($gate->zone)<div class="text-sm text-zinc-500">{{ $gate->zone }}</div>@endif
            
            <!-- Ini yang merender gambar QR Code-nya -->
            <div class="mt-4 w-64 [&>svg]:h-auto [&>svg]:w-full">{!! $svg !!}</div>
            
            <div class="mt-3 text-xs text-zinc-500">Pindai untuk memesan tiket</div>
        </div>

        <!-- Tombol Aksi -->
        <div class="mt-4 flex flex-wrap gap-2 print:hidden">
            <a download="qr-{{ $gate->code }}.svg" href="data:image/svg+xml;base64,{{ base64_encode($svg) }}">
                <x-button>Unduh QR (SVG)</x-button>
            </a>
            <x-button variant="secondary" onclick="window.print()">Cetak</x-button>
        </div>
    @else
        <x-empty-state title="Tidak ada QR baru untuk ditampilkan"
            description="Kembali ke daftar dan klik Terbitkan/Regenerasi untuk memperoleh QR." />
    @endif
    <div class="mt-6 print:hidden"><a class="text-sm font-medium underline" href="{{ route('admin.gates.index') }}">← Kembali ke daftar gerbang</a></div>
</x-layouts.admin>