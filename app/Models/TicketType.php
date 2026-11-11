<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TicketType extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_qty' => 'integer',
            'is_available' => 'boolean',
            'prep_minutes' => 'integer',
        ];
    }

    /**
     * URL publik foto WebP, atau null bila belum ada foto.
     */
    public function photoUrl(): ?string
    {
        return $this->photo_path !== null ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Predikat sellable: tiket tersedia manual dan stok/kuota memadai (> 0).
     *
     * @param  Builder<TicketType>  $query
     */
    public function scopeSellable(Builder $query): void
    {
        $query->where('is_available', true)->where('stock_qty', '>', 0);
    }
}
