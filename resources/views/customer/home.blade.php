<x-layouts.customer :title="'Katalog Tiket: '.$destination">
    <div class="mb-4">
        <h1 class="text-xl font-bold">Katalog Wisata &amp; Tiket</h1>
        <p class="text-xs text-zinc-500">Pilih jenis tiket dan periksa ketersediaan kuota secara real-time.</p>
    </div>

    <livewire:catalog::ticket-catalog :canteen-slug="$destination" />
</x-layouts.customer>