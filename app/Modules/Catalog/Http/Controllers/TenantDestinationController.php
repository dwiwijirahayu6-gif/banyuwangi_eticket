<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantDestinationController extends Controller
{
    public function edit(Tenant $tenant): View
    {
        $destination = $tenant->destinations()->first();

        if (! $destination) {
            $destination = Destination::create([
                'tenant_id' => $tenant->id,
                'name' => $tenant->display_name,
                'slug' => Str::slug($tenant->name),
                'operating_hours' => '08:00 - 16:00 WIB',
            ]);
        }

        return view('catalog::tenant.destination-profile', [
            'tenant' => $tenant,
            'destination' => $destination,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $destination = $tenant->destinations()->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'operating_hours' => ['required', 'string', 'max:100'],
            'facilities' => ['nullable', 'string', 'max:2000'],
            'safety_guidelines' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama destinasi wajib diisi.',
            'operating_hours.required' => 'Jam operasional wajib diisi.',
            'image.image' => 'Berkas harus berupa gambar (JPEG, PNG, JPG, atau WebP).',
            'image.max' => 'Ukuran foto utama maksimal 2 MB.',
            'gallery_images.*.max' => 'Ukuran setiap foto galeri maksimal 2 MB.',
        ]);

        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'operating_hours' => $data['operating_hours'],
            'facilities' => $data['facilities'] ?? null,
            'safety_guidelines' => $data['safety_guidelines'] ?? null,
        ];

        // Kelola Foto Utama
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('destinations', 'public');
            $updateData['image'] = 'storage/'.$path;

            // Hapus foto lama jika berbeda dan tersimpan di storage lokal
            if ($destination->image && Str::startsWith($destination->image, 'storage/destinations/')) {
                $oldPath = Str::after($destination->image, 'storage/');
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        // Kelola Foto Galeri
        if ($request->hasFile('gallery_images')) {
            $existingGallery = is_array($destination->gallery) ? $destination->gallery : [];
            foreach ($request->file('gallery_images') as $file) {
                $galleryPath = $file->store('destinations/gallery', 'public');
                $existingGallery[] = 'storage/'.$galleryPath;
            }
            $updateData['gallery'] = $existingGallery;
        }

        $destination->update($updateData);

        return redirect()->route('tenant.destinasi', ['tenant' => $tenant->code])
            ->with('success', 'Profil destinasi wisata berhasil diperbarui.');
    }

    public function deleteGalleryImage(Tenant $tenant, int $index): RedirectResponse
    {
        $destination = $tenant->destinations()->firstOrFail();
        $gallery = is_array($destination->gallery) ? $destination->gallery : [];

        if (isset($gallery[$index])) {
            $imagePath = $gallery[$index];
            if (Str::startsWith($imagePath, 'storage/destinations/gallery/')) {
                $oldPath = Str::after($imagePath, 'storage/');
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            array_splice($gallery, $index, 1);
            $destination->update(['gallery' => array_values($gallery)]);
        }

        return redirect()->route('tenant.destinasi', ['tenant' => $tenant->code])
            ->with('success', 'Foto galeri berhasil dihapus.');
    }
}
