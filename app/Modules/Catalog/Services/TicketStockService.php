<?php

namespace App\Modules\Catalog\Services;

use App\Models\TicketType;
use App\Modules\Admin\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Perubahan stok tiket bersifat atomik + auditable: update stock_qty, catat movement
 * idempoten (unique idempotency_key), dan tulis audit — dalam satu transaksi + row lock.
 */
final class TicketStockService
{
    public function __construct(private AuditLogger $audit) {}

    public function adjust(TicketType $ticket, int $quantityDelta, string $type, string $idempotencyKey, ?string $reason = null): TicketType
    {
        return DB::transaction(function () use ($ticket, $quantityDelta, $type, $idempotencyKey, $reason): TicketType {
            $locked = TicketType::query()->withoutGlobalScope('tenant')->lockForUpdate()->findOrFail($ticket->id);

            DB::table('ticket_stock_movements')->insert([
                'tenant_id' => $locked->tenant_id,
                'ticket_type_id' => $locked->id,
                'idempotency_key' => $idempotencyKey,
                'type' => $type,
                'quantity_delta' => $quantityDelta,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $locked->forceFill(['stock_qty' => $locked->stock_qty + $quantityDelta])->save();

            $this->audit->record(
                'ticket_type',
                $locked->id,
                'stock_'.$type,
                null,
                ['delta' => $quantityDelta, 'reason' => $reason, 'stock_qty' => $locked->stock_qty],
                $locked->tenant_id
            );

            return $locked;
        });
    }

    public function toggleAvailability(TicketType $ticket): TicketType
    {
        $ticket->forceFill(['is_available' => ! $ticket->is_available])->save();

        $this->audit->record(
            'ticket_type',
            $ticket->id,
            'toggle_availability',
            null,
            ['is_available' => $ticket->is_available],
            $ticket->tenant_id
        );

        return $ticket;
    }
}
