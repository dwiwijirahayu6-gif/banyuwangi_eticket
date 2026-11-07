<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const UPDATED_AT = null;

    // Relasi ke Model Tenant (Pengelola)
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Relasi ke Model TicketType
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    // Relasi ke Model Gate (Gerbang Masuk)
    public function gates(): HasMany
    {
        return $this->hasMany(Gate::class);
    }

    /**
     * Memeriksa apakah destinasi sedang buka berdasarkan jam operasional.
     */
    public function isOpen(?string $currentTime = null): bool
    {
        if (empty($this->operating_hours)) {
            return true;
        }

        if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $this->operating_hours, $matches)) {
            $current = $currentTime ?? now('Asia/Jakarta')->format('H:i');

            return $current >= $matches[1] && $current <= $matches[2];
        }

        return true;
    }

    /**
     * Mengambil jam buka hari ini untuk tampilan informasi.
     */
    public function opensAtToday(): ?string
    {
        if (preg_match('/(\d{1,2}:\d{2})/', (string) $this->operating_hours, $matches)) {
            return str_replace(':', '.', $matches[1]);
        }

        return '08.00';
    }
}
