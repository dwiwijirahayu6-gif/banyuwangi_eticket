<?php

namespace App\Modules\Ordering\Data;

final readonly class CartView
{
    /**
     * @param list<CartLine> $lines
     */
    public function __construct(
        public array $lines,
        public int $subtotal,
        public int $totalTickets,
        public bool $hasBlockingIssues,
        public int $serviceFeeAmount = 0,
    ) {}

    public function grandTotal(): int
    {
        return $this->subtotal + $this->serviceFeeAmount;
    }

    /**
     * Mengelompokkan baris tiket berdasarkan Destinasi Wisata (Tenant)
     *
     * @return list<array{tenant_id: int, destination_name: string, subtotal: int, lines: list<CartLine>}>
     */
    public function tenantGroups(): array
    {
        $groups = [];
        foreach ($this->lines as $line) {
            $groups[$line->tenantId] ??= [
                'tenant_id' => $line->tenantId,
                'destination_name' => $line->destinationName,
                'subtotal' => 0,
                'lines' => [],
            ];
            $groups[$line->tenantId]['lines'][] = $line;
            $groups[$line->tenantId]['subtotal'] += $line->available ? $line->lineTotal : 0;
        }

        return array_values($groups);
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    public function isOrderable(): bool
    {
        return ! $this->isEmpty() && ! $this->hasBlockingIssues;
    }
}