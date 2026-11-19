<x-layouts.admin title="Tambah Destinasi Wisata &amp; Tenant">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Daftarkan Destinasi Wisata &amp; Tenant Baru</h1>
            <p class="text-sm text-zinc-500">Formulir terpadu untuk mendaftarkan destinasi wisata baru sekaligus membuat akun pengelola (Tenant Admin).</p>
        </div>
        <a href="{{ route('admin.tenants.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">
            &larr; Kembali ke Daftar
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800">
            <div class="font-semibold mb-1">Terdapat kesalahan pada formulir pendaftaran:</div>
            <ul class="list-disc ps-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tenants.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- BAGIAN 1: INFORMASI DESTINASI WISATA --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4 mb-5">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">1</span>
                    Informasi Destinasi Wisata
                </h2>
                <p class="text-xs text-zinc-500 mt-1">Informasi ini akan langsung ditampilkan pada katalog publik dan detail wisata untuk pengunjung.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label for="destination_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Nama Destinasi Wisata <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="destination_name" name="destination_name" value="{{ old('destination_name') }}"
                           placeholder="Contoh: Air Terjun Kalibendo, Pantai Boom" required
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <p class="text-xs text-zinc-400 mt-1">Slug URL publik dan nama tenant akan digenerate otomatis berdasarkan nama ini.</p>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Lokasi / Alamat Wisata <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                           placeholder="Contoh: Kecamatan Glagah, Banyuwangi" required
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <div>
                    <label for="operating_hours" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Jam Operasional
                    </label>
                    <input type="text" id="operating_hours" name="operating_hours" value="{{ old('operating_hours', '08:00 - 16:00 WIB') }}"
                           placeholder="08:00 - 16:00 WIB"
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Deskripsi Wisata <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" rows="3" required
                              placeholder="Jelaskan daya tarik, keindahan, dan keunikan destinasi ini..."
                              class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="facilities" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Fasilitas (Opsional)
                    </label>
                    <input type="text" id="facilities" name="facilities" value="{{ old('facilities') }}"
                           placeholder="Contoh: Musholla, Toilet, Gazebo, Warung Makan"
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <div>
                    <label for="safety_guidelines" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Petunjuk Keselamatan (Opsional)
                    </label>
                    <input type="text" id="safety_guidelines" name="safety_guidelines" value="{{ old('safety_guidelines') }}"
                           placeholder="Contoh: Gunakan alas kaki anti selip, dilarang berenang"
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <div class="md:col-span-2">
                    <label for="image" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Foto Sampul Wisata (Maksimal 2MB)
                    </label>
                    <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp"
                           class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: INFORMASI PENGELOLA / TENANT ADMIN --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4 mb-5">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-800 text-xs font-bold">2</span>
                    Informasi Akun Pengelola (Tenant Admin)
                </h2>
                <p class="text-xs text-zinc-500 mt-1">Akun ini dibuat secara otomatis dengan role <code class="text-xs bg-zinc-100 px-1 py-0.5 rounded">tenant_admin</code> dan langsung terhubung dengan tenant yang baru didaftarkan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="admin_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Nama Lengkap Penanggung Jawab / PIC <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}"
                           placeholder="Contoh: Budi Santoso" required
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <div>
                    <label for="admin_phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Nomor Telepon / WhatsApp
                    </label>
                    <input type="text" id="admin_phone" name="admin_phone" value="{{ old('admin_phone') }}"
                           placeholder="Contoh: 081234567890"
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <div>
                    <label for="admin_email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Email Login Admin Tenant <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}"
                           placeholder="admin.kalibendo@wisata.id" required
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <p class="text-xs text-zinc-400 mt-1">Harus unik dan belum pernah digunakan di sistem.</p>
                </div>

                <div>
                    <label for="admin_password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Kata Sandi Awal <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="8"
                           placeholder="Minimal 8 karakter"
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <p class="text-xs text-zinc-400 mt-1">Disimpan secara terenkripsi (Hash::make). Berikan kredensial ini kepada pengelola destinasi.</p>
                </div>
            </div>
        </div>

        {{-- BAGIAN 3: PENGATURAN PLATFORM & KOMISI --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4 mb-5">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-purple-800 text-xs font-bold">3</span>
                    Pengaturan Platform &amp; Keuangan
                </h2>
                <p class="text-xs text-zinc-500 mt-1">Konfigurasi kode identitas tenant, status operasional, serta skema bagi hasil komisi platform.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label for="code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Kode Tenant (Opsional)
                    </label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}"
                           placeholder="Otomatis, cth: TEN-KALIBENDO"
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm uppercase font-mono focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <p class="text-xs text-zinc-400 mt-1">Kosongkan jika ingin digenerate otomatis dari nama wisata.</p>
                </div>

                <div>
                    <label for="commission_rate" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Komisi Platform (%) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="commission_rate" name="commission_rate" step="0.01" min="0" max="100"
                           value="{{ old('commission_rate', '5.00') }}" required
                           class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <p class="text-xs text-zinc-400 mt-1">Nilai antara 0% s/d 100%.</p>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Status Awal Tenant
                    </label>
                    <select id="status" name="status"
                            class="w-full rounded-lg border border-zinc-300 px-3.5 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif (Tampil di Katalog)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif (Draft / Belum Tampil)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- TOMBOL AKSI --}}
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.tenants.index') }}" 
               class="px-5 py-2.5 rounded-lg border border-zinc-300 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 transition">
                Batal
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Daftarkan Wisata &amp; Akun Tenant
            </button>
        </div>
    </form>
</x-layouts.admin>
