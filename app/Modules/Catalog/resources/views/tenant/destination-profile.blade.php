<x-layouts.tenant :title="'Profil Destinasi — ' . $destination->name" :tenant="$tenant">
    <div class="max-w-4xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Profil Destinasi Wisata</h2>
            <p class="text-sm text-slate-500">Kelola identitas, deskripsi, foto atraksi, jam operasional, serta petunjuk keselamatan untuk wisatawan.</p>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.destinasi.update', ['tenant' => $tenant->code]) }}" 
              enctype="multipart/form-data" 
              class="space-y-6 bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-xs">
            @csrf
            @method('PUT')

            {{-- 1. Identitas Pokok Destinasi --}}
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Umum</h3>

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Destinasi Wisata <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $destination->name) }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="location" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Lokasi / Alamat Lengkap</label>
                        <input type="text" id="location" name="location" value="{{ old('location', $destination->location) }}" placeholder="Contoh: Desa Sragi, Kecamatan Songgon"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">
                        @error('location') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="operating_hours" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jam Operasional <span class="text-red-500">*</span></label>
                        <input type="text" id="operating_hours" name="operating_hours" value="{{ old('operating_hours', $destination->operating_hours) }}" required placeholder="Contoh: 08:00 - 16:00 WIB"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">
                        @error('operating_hours') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi Lengkap Destinasi</label>
                    <textarea id="description" name="description" rows="4" placeholder="Ceritakan keindahan, daya tarik, dan keunikan wisata ini..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">{{ old('description', $destination->description) }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 2. Fasilitas & Petunjuk Keselamatan --}}
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Fasilitas & Keselamatan Wisatawan</h3>

                <div>
                    <label for="facilities" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Fasilitas yang Tersedia</label>
                    <textarea id="facilities" name="facilities" rows="3" placeholder="Contoh: Gazebo istirahat, Musholla, Toilet bersih, Area parkir luas, Warung makan warga"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">{{ old('facilities', $destination->facilities) }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Pisahkan dengan koma atau baris baru untuk memudahkan wisatawan membaca fasilitas.</p>
                    @error('facilities') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="safety_guidelines" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Petunjuk & Aturan Keselamatan Pengunjung</label>
                    <textarea id="safety_guidelines" name="safety_guidelines" rows="3" placeholder="Contoh: Wajib mengenakan alas kaki anti-selip, Dilarang berenang di bawah pusaran air terjun saat hujan, Patuhi instruksi pemandu lapangan"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition">{{ old('safety_guidelines', $destination->safety_guidelines) }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Petunjuk ini membantu menjaga keselamatan wisatawan selama berkunjung di destinasi Anda.</p>
                    @error('safety_guidelines') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 3. Foto Utama & Galeri --}}
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">Foto Utama & Galeri</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Foto Utama Destinasi</label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        @if ($destination->image)
                            <img src="{{ asset($destination->image) }}" alt="Foto Utama" 
                                 class="h-28 w-44 rounded-xl object-cover border border-slate-200 shadow-xs">
                        @else
                            <div class="flex h-28 w-44 items-center justify-center rounded-xl bg-slate-100 text-slate-400 text-xs border border-dashed border-slate-300">
                                Belum ada foto
                            </div>
                        @endif

                        <div class="space-y-1.5 flex-1">
                            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400">Format: JPG, JPEG, PNG, WebP. Maksimal 2 MB. Disarankan rasio 16:9.</p>
                            @error('image') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Tambah Foto Galeri</label>
                    <input type="file" name="gallery_images[]" id="gallery_images" multiple accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">Anda dapat memilih beberapa foto sekaligus untuk menambah ke galeri.</p>
                    @error('gallery_images.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    @if (!empty($destination->gallery) && is_array($destination->gallery))
                        <div class="mt-4">
                            <p class="text-xs font-semibold text-slate-700 mb-2">Foto Galeri Saat Ini ({{ count($destination->gallery) }} Foto):</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach ($destination->gallery as $idx => $photo)
                                    <div class="relative group rounded-xl overflow-hidden border border-slate-200 shadow-2xs">
                                        <img src="{{ asset($photo) }}" alt="Galeri {{ $idx + 1 }}" class="h-24 w-full object-cover">
                                        <button type="submit" form="delete-gallery-{{ $idx }}" 
                                                class="absolute top-1.5 right-1.5 h-6 w-6 rounded-full bg-red-600/90 text-white flex items-center justify-center opacity-90 hover:opacity-100 hover:bg-red-700 transition shadow-sm"
                                                onclick="return confirm('Hapus foto ini dari galeri destinasi?')" title="Hapus foto">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('tenant.dashboard', ['tenant' => $tenant->code]) }}" 
                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Perubahan Profil</span>
                </button>
            </div>
        </form>

        {{-- Hidden Forms for Gallery Deletions --}}
        @if (!empty($destination->gallery) && is_array($destination->gallery))
            @foreach ($destination->gallery as $idx => $photo)
                <form id="delete-gallery-{{ $idx }}" method="POST" action="{{ route('tenant.destinasi.gallery.delete', ['tenant' => $tenant->code, 'index' => $idx]) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    </div>
</x-layouts.tenant>
