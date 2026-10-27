<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gate extends Model
{
    protected $guarded = [];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(GateQrToken::class);
    }

    public function activeToken(): HasOne
    {
        return $this->hasOne(GateQrToken::class)->where('status', 'active')->latestOfMany();
    }
}