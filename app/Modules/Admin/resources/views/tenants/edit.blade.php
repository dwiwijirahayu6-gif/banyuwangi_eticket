<x-layouts.admin title="Detail &amp; Pengaturan Tenant">
    @if (session('status'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800">
            <div class="font-semibold mb-1">Terdapat kesalahan:</div>
            <ul class="list-disc ps-5 space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $destination = $tenant->destination;
    @endphp

    {{-- Header Detail & Aksi Status --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $destination->name ?? $tenant->name }}
                </h1>
                @if($tenant->status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                        AKTIF
                    </span>
                @elseif($tenant->status === 'suspended')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                        SUSPENDED
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        NONAKTIF
                    </span>
                @endif
            </div>
            <p class="text-xs text-zinc-500 font-mono">Kode Tenant: {{ $tenant->code }} &bull; Terdaftar: {{ $tenant->created_at?->format('d F Y, H:i') ?? '-' }}</p>
        </div>

        {{-- Kontrol Status (Aktif / Nonaktif / Suspend) --}}
        <div class="flex items-center gap-2">
            @if($tenant->status !== 'active')
                <form method="POST" action="{{ route('admin.tenants.status', $tenant) }}">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition">
                        Aktifkan Tenant
                    </button>
                </form>
            @endif

            @if($tenant->status !== 'inactive')
                <form method="POST" action="{{ route('admin.tenants.status', $tenant) }}">
                    @csrf
                    <input type="hidden" name="status" value="inactive">
                    <button type="submit" onclick="return confirm('Nonaktifkan tenant ini? Destinasi tidak akan muncul di katalog publik.')" class="px-3.5 py-2 bg-zinc-600 hover:bg-zinc-700 text-white text-xs font-semibold rounded-lg transition">
                        Nonaktifkan
                    </button>
                </form>
            @endif

            @if($tenant->status !== 'suspended')
                <form method="POST" action="{{ route('admin.tenants.status', $tenant) }}">
                    @csrf
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" onclick="return confirm('Tangguhkan (Suspend) tenant ini? Seluruh akses booking dan portal akan dihentikan.')" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition">
                        Suspend
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- KOLOM KIRI (2 Kolom): EDIT INFORMASI DESTINASI --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
                    Edit Informasi Destinasi Wisata
                </h2>

                <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">Nama Destinasi Wisata</label>
                            <input type="text" name="destination_name" value="{{ old('destination_name', $destination->name ?? $tenant->name) }}" required
                                   class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">Lokasi / Alamat</label>
                            <input type="text" name="location" value="{{ old('location', $destination->location ?? '') }}" required
                                   class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">Jam Operasional</label>
                            <input type="text" name="operating_hours" value="{{ old('operating_hours', $destination->operating_hours ?? '08:00 - 16:00 WIB') }}"
                                   class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">Deskripsi Wisata</label>
                            <textarea name="description" rows="3" required
                                      class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ old('description', $destination->description ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">Fasilitas</label>
                            <input type="text" name="facilities" value="{{ old('facilities', $destination->facilities ?? '') }}"
                                   class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">Petunjuk Keselamatan</label>
                            <input type="text" name="safety_guidelines" value="{{ old('safety_guidelines', $destination->safety_guidelines ?? '') }}"
                                   class="w-full rounded-lg border border-zinc-300 px-3.5 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-400 mb-1">Perbarui Foto Sampul</label>
                            @if($destination?->image)
                                <div class="mb-2">
                                    <span class="text-xs text-zinc-500">Foto saat ini: </span>
                                    <span class="text-xs font-mono text-zinc-700 dark:text-zinc-300">{{ $destination->image }}</span>
                                </div>
                            @endif
                            <input type="file" name="image" accept="image/png,image/jpeg,image/webp"
                                   class="w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                        </div>
                    </div>

                    <div class="pt-3 text-right">
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Simpan Perubahan Destinasi
                        </button>
                    </div>
                </form>
            </div>

            {{-- AKUN PENGELOLA (ADMIN TENANT) --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
                    Akun Pengelola / Petugas Terdaftar
                </h2>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-800 mb-5">
                    @forelse($adminUsers as $user)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                <p class="text-xs font-mono text-zinc-500">{{ $user->email }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 uppercase">
                                    {{ $user->role }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-600' }}">
                                    {{ strtoupper($user->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-500 py-2">Belum ada akun tenant_admin yang terikat langsung.</p>
                    @endforelse
                </div>

                {{-- Operator / Role Mapping --}}
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">Penugasan Role Pengelola (UserTenantRole)</h3>
                <ul class="space-y-2 mb-4 text-xs">
                    @foreach ($members as $m)
                        <li class="flex items-center justify-between p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $m->user?->email }}</span>
                            <div class="flex items-center gap-2">
                                <span class="bg-zinc-200 dark:bg-zinc-700 px-2 py-0.5 rounded text-[11px] font-semibold text-zinc-700 dark:text-zinc-300 uppercase">{{ $m->role }}</span>
                                <form method="POST" action="{{ route('admin.tenants.roles.destroy', [$tenant, $m->user]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline text-[11px]">Hapus</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('admin.tenants.roles.store', $tenant) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-end pt-2">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400 mb-1">Email Pengguna</label>
                        <input type="email" name="email" required placeholder="user@email.com"
                               class="w-full text-xs rounded border border-zinc-300 px-2.5 py-1.5 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400 mb-1">Role Penugasan</label>
                        <select name="role" required class="w-full text-xs rounded border border-zinc-300 px-2.5 py-1.5 focus:border-blue-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800">
                            <option value="owner">Owner (Pemilik)</option>
                            <option value="operator">Operator (Petugas)</option>
                            <option value="cashier">Cashier (Kasir)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full px-3 py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white text-xs font-semibold rounded transition">
                        Tugaskan Role
                    </button>
                </form>
            </div>
        </div>

        {{-- KOLOM KANAN (1 Kolom): KOMISI & REKENING BANK --}}
        <div class="space-y-6">
            {{-- Pengaturan Komisi Platform --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-2">Skema Komisi Platform</h2>
                <div class="p-3 bg-purple-50 dark:bg-purple-950/30 rounded-lg border border-purple-200 dark:border-purple-900/50 mb-4">
                    <p class="text-xs text-purple-700 dark:text-purple-300">Komisi Aktif Saat Ini:</p>
                    <p class="text-xl font-bold text-purple-800 dark:text-purple-200">
                        {{ $activeCommission ? number_format($activeCommission->commission_rate * 100, 2) . '%' : ($activeCommission?->percentage ? number_format($activeCommission->percentage, 2) . '%' : '5.00%') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.tenants.commission.store', $tenant) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Persentase Baru (%)</label>
                        <input type="number" name="commission_rate" step="0.01" min="0" max="100" required placeholder="Contoh: 7.5"
                               class="w-full rounded border border-zinc-300 px-3 py-2 text-xs focus:border-purple-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Mulai Berlaku</label>
                        <input type="datetime-local" name="effective_at" required
                               class="w-full rounded border border-zinc-300 px-3 py-2 text-xs focus:border-purple-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800">
                    </div>
                    <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg transition">
                        Jadwalkan Perubahan Komisi
                    </button>
                </form>
            </div>

            {{-- Rekening Bank Pengelola --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-3">Rekening Bank Penarikan</h2>
                <ul class="space-y-2 mb-4 text-xs">
                    @forelse ($bankAccounts as $acc)
                        <li class="p-3 border border-zinc-200 dark:border-zinc-800 rounded-lg flex items-center justify-between">
                            <div>
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $acc->bank_code }}</span> 
                                <span class="font-mono text-zinc-500">&bull;&bull;&bull; {{ $acc->account_last4 }}</span>
                                <p class="text-[11px] text-zinc-600 dark:text-zinc-400">{{ $acc->account_holder }}</p>
                            </div>
                            <div class="text-right">
                                @if($acc->is_primary)
                                    <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">UTAMA</span>
                                @endif
                                <span class="text-[10px] {{ $acc->status === 'verified' ? 'text-emerald-600' : 'text-amber-600' }} block font-semibold mt-1">
                                    {{ strtoupper($acc->status) }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="text-xs text-zinc-500 py-2">Belum ada rekening bank yang terdaftar.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('admin.tenants.bank.store', $tenant) }}" class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Bank (BCA/BNI/Mandiri)</label>
                        <input type="text" name="bank_code" required placeholder="BCA" class="w-full text-xs rounded border border-zinc-300 px-2 py-1.5 dark:border-zinc-700 dark:bg-zinc-800">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Nama Pemilik Rekening</label>
                        <input type="text" name="account_holder" required placeholder="Nama Pemilik" class="w-full text-xs rounded border border-zinc-300 px-2 py-1.5 dark:border-zinc-700 dark:bg-zinc-800">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Nomor Rekening</label>
                        <input type="text" name="account_number" required placeholder="1234567890" class="w-full text-xs rounded border border-zinc-300 px-2 py-1.5 dark:border-zinc-700 dark:bg-zinc-800">
                    </div>
                    <button type="submit" class="w-full py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white text-xs font-semibold rounded transition">
                        Tambah Rekening
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
