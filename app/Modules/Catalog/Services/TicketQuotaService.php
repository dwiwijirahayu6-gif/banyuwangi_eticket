<?php

namespace App\Modules\Catalog\Services;

use App\Models\TicketType;

class TicketQuotaService
{
    public function __construct(private TicketStockService $stockService) {}

    public function adjust(TicketType $ticket, int $quantityDelta, string $type, string $idempotencyKey, ?string $reason = null): TicketType
    {
        return $this->stockService->adjust($ticket, $quantityDelta, $type, $idempotencyKey, $reason);
    }

    public function toggleAvailability(TicketType $ticket): TicketType
    {
        return $this->stockService->toggleAvailability($ticket);
    }
}
