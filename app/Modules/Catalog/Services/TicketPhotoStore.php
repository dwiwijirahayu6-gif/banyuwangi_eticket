<?php

namespace App\Modules\Catalog\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class TicketPhotoStore
{
    public const MAX_SIDE = 800;

    public const MAX_SIZE = 2 * 1024 * 1024;

    public function store(UploadedFile $file, int $tenantId): string
    {
        if ($file->getSize() > self::MAX_SIZE) {
            throw new RuntimeException(
                'Ukuran foto maksimal 2 MB.'
            );
        }

        $mime = $file->getMimeType();

        if (! in_array($mime, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true)) {
            throw new RuntimeException(
                'Format foto harus JPG, PNG, atau WebP.'
            );
        }

        if (
            ! function_exists('imagecreatefromstring')
            || ! function_exists('imagewebp')
            || ! function_exists('imagescale')
        ) {
            throw new RuntimeException(
                'Ekstensi GD PHP belum tersedia.'
            );
        }

        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException(
                'Foto gagal dibaca.'
            );
        }

        $source = imagecreatefromstring($contents);

        if ($source === false) {
            throw new RuntimeException(
                'Berkas bukan gambar yang valid.'
            );
        }

        [$width, $height] = [
            imagesx($source),
            imagesy($source),
        ];

        $scale = min(
            1,
            self::MAX_SIDE / max($width, $height)
        );

        $target = imagescale(
            $source,
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale))
        );

        imagedestroy($source);

        if ($target === false) {
            throw new RuntimeException(
                'Gambar gagal diperkecil.'
            );
        }

        ob_start();

        $success = imagewebp($target, null, 80);

        $webp = ob_get_clean();

        imagedestroy($target);

        if (! $success || ! is_string($webp) || $webp === '') {
            throw new RuntimeException(
                'Konversi gambar ke WebP gagal.'
            );
        }

        $path = "tickets/{$tenantId}/".Str::uuid().'.webp';

        $saved = Storage::disk('public')->put($path, $webp);

        if (! $saved) {
            throw new RuntimeException(
                'Foto gagal disimpan.'
            );
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }
}