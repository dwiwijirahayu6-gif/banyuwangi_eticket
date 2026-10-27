<x-layouts.admin title="Daftarkan Tenant">
    <h1 class="text-xl font-semibold">Daftarkan Tenant Baru</h1>
    
    @if ($errors->any())
        <div class="mt-3 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tenants.store') }}" class="mt-6 grid max-w-3xl gap-6 md:grid-cols-2">
        @csrf
        <fieldset class="space-y-4">
            <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-zinc-500">Identitas Usaha</legend>
            <x-input name="name" label="Nama Destinasi / Tenant" value="{{ old('name') }}" required />
            <x-input name="code" label="Kode Unik (contoh: BWI)" value="{{ old('code') }}" required />
            <x-input name="commission_rate" label="Komisi Awal (%)" type="number" step="0.01" value="{{ old('commission_rate', '10') }}" required />
        </fieldset>

        <div class="space-y-6">
            <fieldset class="space-y-4">
                <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-zinc-500">Penanggung Jawab</legend>
                <x-input name="pic_name" label="Nama" value="{{ old('pic_name') }}" required />
                <x-input name="pic_email" label="Surel (menerima tautan atur kata sandi)" type="email" value="{{ old('pic_email') }}" required />
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-zinc-500">Rekening Bank</legend>
                <x-input name="bank_code" label="Bank" value="{{ old('bank_code') }}" required />
                <x-input name="account_holder" label="Nama pemilik" value="{{ old('account_holder') }}" required />
                <x-input name="account_number" label="Nomor rekening" inputmode="numeric" required />
            </fieldset>
        </div>

        <div class="md:col-span-2"><x-button type="submit">Simpan &amp; kirim kredensial awal</x-button></div>
    </form>
</x-layouts.admin>