<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    public const STATUS_LABELS = [
        'pending_payment' => 'Menunggu Pembayaran',
        'paid' => 'Lunas',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kedaluwarsa',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Alasan pesanan tidak bisa dibatalkan, atau null jika boleh dibatalkan.
     *
     * Aturan: pesanan yang belum dibayar selalu boleh dibatalkan. Pesanan yang
     * sudah dibayar hanya boleh dibatalkan paling lambat H-1 sebelum tanggal
     * kunjungan dan selama belum ada tiket yang dipakai (check-in).
     */
    public function cancellationBlockedReason(): ?string
    {
        return match ($this->status) {
            'pending_payment' => null,
            'paid' => $this->paidCancellationBlockedReason(),
            'cancelled' => 'Pesanan ini sudah dibatalkan.',
            'expired' => 'Pesanan ini sudah kedaluwarsa.',
            default => 'Pesanan ini sudah selesai dan tidak dapat dibatalkan.',
        };
    }

    public function canBeCancelled(): bool
    {
        return $this->cancellationBlockedReason() === null;
    }

    private function paidCancellationBlockedReason(): ?string
    {
        if ($this->tickets()->where('status', 'checked_in')->exists()) {
            return 'Sebagian tiket sudah digunakan sehingga pesanan tidak dapat dibatalkan.';
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        if (! $this->visit_date || $this->visit_date->toDateString() <= $today) {
            return 'Pembatalan tiket yang sudah dibayar hanya dapat dilakukan paling lambat H-1 sebelum tanggal kunjungan.';
        }

        return null;
    }
}