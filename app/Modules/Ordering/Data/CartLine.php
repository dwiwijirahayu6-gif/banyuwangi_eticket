<?php

namespace App\Modules\Ordering\Data;

/**
 * Baris keranjang tiket HASIL REVALIDASI dari basis data.
 * Harga tiket, kuota harian, dan ketersediaan destinasi selalu dihitung ulang dari database.
 */
final readonly class CartLine
{
    /**
     * @param list<array{id: int, name: string, price_delta: int}> $addons
     * @param list<string> $issues mis. ticket_unavailable|insufficient_quota|price_changed
     */
    public function __construct(
        public string $lineKey,
        public int $ticketTypeId,
        public int $tenantId,
        public int $visitSlotId, // <--- TAMBAHAN BARU UNTUK MODUL 9
        public string $destinationName,
        public string $ticketTypeName,
        public string $visitDate,
        public int $quantity,
        public int $unitPrice,
        public int $priceAtAdd,
        public array $addons,
        public int $addonTotal,
        public int $lineTotal,
        public bool $available,
        public array $issues,
        public ?string $note = null,
    ) {}

    public function priceChanged(): bool
    {
        return in_array('price_changed', $this->issues, true);
    }
}