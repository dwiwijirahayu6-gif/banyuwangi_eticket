<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $guarded = [];

    public const UPDATED_AT = null;

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function visitSlots(): HasMany
    {
        return $this->hasMany(VisitSlot::class);
    }
}
