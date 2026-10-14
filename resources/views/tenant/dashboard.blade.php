<x-layouts.tenant title="Dashboard">
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-semibold">Dashboard Pengelola Wisata</h1>
        <x-status-badge status="active">aktif</x-status-badge>
    </div>
    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
        Pengelola: <span class="font-mono">{{ $tenant }}</span>
    </p>
    <div class="mt-6">
        <x-empty-state title="Belum ada aktivitas"
            description="Destinasi, pesanan tiket, dan validasi akan tampil di sini (Modul 7 & 12)." />
    </div>
</x-layouts.tenant>