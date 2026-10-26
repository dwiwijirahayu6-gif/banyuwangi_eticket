<x-layouts.admin title="Daftarkan Tenant">
    <h1 class="text-xl font-semibold">Daftarkan Tenant Baru</h1>
    
    @if ($errors->any())
        <div class="mt-3 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">
            <ul class="list-disc ps-4">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tenants.store') }}" class="mt-6 max-w-md space-y-4">
        @csrf
        <x-input name="name" label="Nama Destinasi / Tenant" value="{{ old('name') }}" required />
        <x-input name="code" label="Kode Unik (contoh: BWI)" value="{{ old('code') }}" required />
        <x-input name="commission_rate" label="Komisi Awal (%)" type="number" step="0.01" value="{{ old('commission_rate', '10') }}" required />
        <x-button type="submit">Simpan</x-button>
    </form>
</x-layouts.admin>