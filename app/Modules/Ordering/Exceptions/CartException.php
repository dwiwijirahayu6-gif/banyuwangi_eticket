<?php

namespace App\Modules\Ordering\Exceptions;

use RuntimeException;

final class CartException extends RuntimeException
{
    public static function ticketNotOrderable(): self
    {
        return new self('Tiket wisata tidak tersedia untuk dipesan.');
    }

    public static function invalidQuantity(): self
    {
        return new self('Jumlah tiket tidak valid.');
    }

    public static function tooManyLines(): self
    {
        return new self('Keranjang penuh. Selesaikan pemesanan tiket Anda terlebih dahulu.');
    }

    public static function quotaExceeded(): self
    {
        return new self('Kuota pengunjung untuk tanggal kunjungan ini telah penuh.');
    }

    public static function invalidVisitDate(): self
    {
        return new self('Tanggal kunjungan tidak valid.');
    }
}