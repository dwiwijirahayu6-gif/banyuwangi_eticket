<x-layouts.tenant title="Kelola Tiket Wisata — {{ $tenant->display_name }}">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Kelola Tiket Wisata</h1>
            <p class="text-sm text-zinc-500">Atur jenis tiket, harga, ketersediaan cepat, kuota, dan foto atraksi destinasi Anda.</p>
        </div>
    </div>

    <livewire:catalog::ticket-manager :tenant-id="$tenant->id" />
</x-layouts.tenant>
