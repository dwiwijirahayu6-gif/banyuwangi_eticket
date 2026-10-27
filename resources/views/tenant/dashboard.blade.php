<x-layouts.admin title="Dashboard">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Dashboard Admin</h1>
        <a href="{{ route('admin.tenants.index') }}">
            <x-button>Kelola Destinasi (Tenant)</x-button>
        </a>
    </div>

    <div class="rounded-xl border border-zinc-200 p-6">
        <h2 class="font-semibold mb-4">Selamat Datang, {{ auth()->user()->name }}</h2>
        <p class="text-sm text-zinc-500 mb-4">
            Ini adalah pusat kendali Sistem Pemesanan Tiket Wisata Banyuwangi. 
            Gunakan menu di atas untuk mengelola destinasi dan skema komisi.
        </p>
    </div>
</x-layouts.admin>