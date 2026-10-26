<x-layouts.admin title="Detail Tenant">
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mb-6 flex items-center gap-3">
        <h1 class="text-xl font-semibold">{{ $tenant->name }}</h1>
        <x-status-badge :status="$tenant->status === 'active' ? 'active' : 'default'">{{ strtoupper($tenant->status) }}</x-status-badge>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <!-- Info Tenant -->
        <div class="rounded-xl border border-zinc-200 p-6">
            <h2 class="mb-4 font-semibold">Informasi Tenant</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">Kode</dt><dd class="font-mono">{{ $tenant->code }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Dibuat pada</dt><dd>{{ $tenant->created_at?->format('d M Y H:i') }}</dd></div>
            </dl>
        </div>

        <!-- Skema Komisi -->
        <div class="rounded-xl border border-zinc-200 p-6">
            <h2 class="mb-4 font-semibold">Ubah Komisi</h2>
            <p class="text-sm mb-3">Komisi aktif saat ini: <strong>{{ $activeCommission ? number_format($activeCommission->commission_rate * 100, 2) . '%' : 'Belum ada' }}</strong></p>
            
            <form method="POST" action="{{ route('admin.tenants.commission.store', $tenant) }}" class="space-y-3">
                @csrf
                <x-input name="commission_rate" label="Persentase komisi baru (%)" type="number" step="0.01" required />
                <x-input name="effective_at" label="Tanggal berlaku (masa mendatang)" type="datetime-local" required />
                <x-button type="submit" class="w-full">Jadwalkan Komisi</x-button>
            </form>
        </div>
    </div>

    <!-- Rekening Bank -->
    <div class="mt-6 rounded-xl border border-zinc-200 p-6">
        <h2 class="mb-4 font-semibold">Rekening Bank</h2>
        <ul class="mb-6 space-y-3 text-sm">
            @forelse ($bankAccounts as $acc)
                <li class="flex flex-col md:flex-row md:items-center justify-between border-b pb-3 gap-2">
                    <div>
                        <span class="font-medium">{{ $acc->bank_code }}</span> ••• {{ $acc->account_last4 }} ••• 
                        <span class="text-zinc-600">{{ $acc->account_holder }}</span>
                        @if ($acc->is_primary)
                            <span class="ml-2 text-xs font-semibold bg-blue-100 text-blue-800 px-2 py-1 rounded">PRIMARY</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <x-status-badge :status="$acc->status === 'verified' ? 'active' : 'pending'">{{ $acc->status }}</x-status-badge>
                        
                        @if ($acc->status !== 'verified')
                            <form method="POST" action="{{ route('admin.tenants.bank.verify', [$tenant, $acc]) }}">
                                @csrf
                                <input type="hidden" name="approve" value="1">
                                <button class="text-xs text-green-600 hover:underline">Verifikasi</button>
                            </form>
                        @endif

                        @if (!$acc->is_primary)
                            <form method="POST" action="{{ route('admin.tenants.bank.primary', [$tenant, $acc]) }}">
                                @csrf
                                <button class="text-xs text-blue-600 hover:underline">Jadikan Utama</button>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="text-zinc-500">Belum ada rekening terdaftar.</li>
            @endforelse
        </ul>

        <h3 class="text-sm font-semibold mb-3">Tambah Rekening Baru</h3>
        <form method="POST" action="{{ route('admin.tenants.bank.store', $tenant) }}" class="grid gap-3 md:grid-cols-4 items-end">
            @csrf
            <x-input name="bank_code" label="Kode Bank (BCA/BNI)" required />
            <x-input name="account_holder" label="Nama Pemilik" required />
            <x-input name="account_number" label="Nomor Rekening" required />
            <x-button type="submit">Tambah</x-button>
        </form>
    </div>

    <!-- Riwayat Komisi -->
    <div class="mt-6 rounded-xl border border-zinc-200 p-6">
        <h2 class="mb-4 font-semibold">Riwayat Perubahan Komisi</h2>
        <ul class="space-y-2 text-sm">
            @forelse ($commissions as $c)
                <li class="flex justify-between border-b pb-2">
                    <span class="font-medium">{{ number_format($c->commission_rate * 100, 2) }}%</span>
                    <span class="text-zinc-500 text-xs">
                        Berlaku: {{ $c->valid_from->format('d M Y H:i') }} 
                        {{ $c->valid_to ? ' s/d ' . $c->valid_to->format('d M Y H:i') : ' (Aktif)' }}
                    </span>
                </li>
            @empty
                <li class="text-zinc-500">Belum ada riwayat komisi.</li>
            @endforelse
        </ul>
    </div>

    <!-- Operator / Role -->
    <div class="mt-6 rounded-xl border border-zinc-200 p-6">
        <h2 class="mb-4 font-semibold">Operator / Role</h2>
        <ul class="mb-4 space-y-2 text-sm">
            @foreach ($members as $m)
                <li class="flex items-center justify-between border-b pb-2">
                    <span>{{ $m->user->email }}</span>
                    <span class="text-xs bg-zinc-100 px-2 py-1 rounded">{{ $m->role }}</span>
                </li>
            @endforeach
        </ul>
        <form method="POST" action="{{ route('admin.tenants.roles.store', $tenant) }}" class="flex flex-wrap items-end gap-2">
            @csrf
            <x-input name="email" label="Email User" type="email" required />
            <x-input name="role" label="Role (owner/operator/cashier)" required />
            <x-button type="submit" variant="secondary">Tugaskan</x-button>
        </form>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.tenants.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Daftar Tenant</a>
    </div>
</x-layouts.admin>