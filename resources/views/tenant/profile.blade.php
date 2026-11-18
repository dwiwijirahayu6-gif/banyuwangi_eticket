<x-layouts.tenant :title="'Pengaturan Akun — ' . $tenant->display_name" :tenant="$tenant">
    <div class="max-w-2xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Pengaturan Profil Pengelola</h2>
            <p class="text-sm text-slate-500">Kelola identitas akun operator dan perbarui kata sandi akun Anda.</p>
        </div>

        {{-- Ringkasan Identitas Tenant & Role --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identitas Hak Akses</h3>
            <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                <div>
                    <span class="text-slate-500 block">Destinasi Dikelola:</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $destination->name ?? $tenant->name }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Kode Tenant:</span>
                    <span class="font-mono font-bold text-blue-600 text-sm">{{ $tenant->code }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Alamat Surel:</span>
                    <span class="font-medium text-slate-900">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Peran Sistem:</span>
                    <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                        {{ $user->role === 'tenant_admin' ? 'Pengelola Destinasi (Tenant Admin)' : ucfirst($user->role) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form Edit Profil & Kata Sandi --}}
        <form method="POST" action="{{ route('tenant.profil.update', ['tenant' => $tenant->code]) }}" 
              class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap Operator <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Surel (Email)</label>
                <input type="email" id="email" value="{{ $user->email }}" disabled
                       class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                <p class="text-[11px] text-slate-400 mt-1">Perubahan alamat email hanya dapat dilakukan melalui Administrator Pusat.</p>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Perbarui Kata Sandi (Opsional)</h3>
                <p class="text-[11px] text-slate-500 -mt-2">Kosongkan jika Anda tidak ingin mengubah kata sandi.</p>

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Saat Ini</label>
                    <input type="password" id="current_password" name="current_password"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                        <input type="password" id="password" name="password" minlength="8"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-blue-600 transition">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Pengaturan Akun</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.tenant>
