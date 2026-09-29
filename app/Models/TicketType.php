<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketType extends Model
{
    protected $guarded = [];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}