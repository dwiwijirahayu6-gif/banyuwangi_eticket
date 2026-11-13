<?php

namespace App\Modules\Ordering\Exceptions;

use RuntimeException;

final class CheckoutException extends RuntimeException
{
    public static function emptyCart(): self
    {
        return new self('Keranjang pemesanan tiket kosong.');
    }

    public static function cartNotOrderable(): self
    {
        return new self('Ada jenis tiket yang tidak dapat dipesan. Perbarui keranjang lalu coba lagi.');
    }

    public static function insufficientStock(): self
    {
        return new self('Kuota tiket berubah saat checkout. Perbarui keranjang lalu coba lagi.');
    }

    public static function commissionNotConfigured(): self
    {
        return new self('Konfigurasi bagi hasil untuk pengelola destinasi belum tersedia.');
    }
}