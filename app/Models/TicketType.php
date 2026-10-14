<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketType extends Model
{
    protected $guarded = [];

    public const UPDATED_AT = null;

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
