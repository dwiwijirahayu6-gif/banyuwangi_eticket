<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    protected $guarded = [];

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
}