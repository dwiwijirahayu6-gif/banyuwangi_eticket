<?php

namespace App\Modules\Ordering\Services;

use App\Models\CustomerSession;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Models\VisitSlot;
use App\Modules\Ordering\Data\CartLine;
use App\Modules\Ordering\Data\CartView;
use App\Modules\Ordering\Exceptions\CartException;
use Illuminate\Support\Facades\Redis;

final class CartService
{
    private const KEY_PREFIX = 'cart:';
    private const MAX_QTY_PER_LINE = 10;
    private const MAX_LINES = 20;
    private const FALLBACK_TTL_SECONDS = 14400; // 4 jam

    /**
     * Menambahkan tiket wisata ke keranjang Redis.
     */
    public function add(CustomerSession $session, int $ticketTypeId, string $visitDate, int $quantity = 1, ?string $note = null): void
    {
        if ($quantity < 1) {
            throw CartException::invalidQuantity();
        }

        $ticketType = TicketType::query()
            ->withoutGlobalScope('tenant')
            ->with('tenant:id,display_name,status')
            ->find($ticketTypeId);

        if ($ticketType === null || $ticketType->tenant?->status !== 'active') {
            throw CartException::ticketNotOrderable();
        }

        $lineKey = $this->lineKey($ticketTypeId, $visitDate, $note);
        $items = $this->load($session);
        $existingQty = isset($items[$lineKey]) ? (int) $items[$lineKey]['quantity'] : 0;
        $newQty = min($existingQty + $quantity, self::MAX_QTY_PER_LINE);

        if (! isset($items[$lineKey]) && count($items) >= self::MAX_LINES) {
            throw CartException::tooManyLines();
        }

        $items[$lineKey] = [
            'line_key' => $lineKey,
            'ticket_type_id' => $ticketType->id,
            'tenant_id' => $ticketType->tenant_id,
            'visit_date' => $visitDate,
            'quantity' => $newQty,
            'note' => $note,
            'price_at_add' => (int) $ticketType->price,
            'destination_name' => (string) $ticketType->tenant->display_name,
            'ticket_type_name' => (string) $ticketType->name,
        ];

        $this->persist($session, $items);
    }

    public function setQuantity(CustomerSession $session, string $lineKey, int $quantity): void
    {
        $items = $this->load($session);
        if (! isset($items[$lineKey])) {
            return;
        }
        if ($quantity < 1) {
            unset($items[$lineKey]);
        } else {
            $items[$lineKey]['quantity'] = min($quantity, self::MAX_QTY_PER_LINE);
        }
        $this->persist($session, $items);
    }

    public function remove(CustomerSession $session, string $lineKey): void
    {
        $items = $this->load($session);
        if (isset($items[$lineKey])) {
            unset($items[$lineKey]);
            $this->persist($session, $items);
        }
    }

    public function clear(CustomerSession $session): void
    {
        Redis::del($this->key($session));
    }

    /**
     * Menyusun tampilan keranjang tiket TERREVALIDASI dari basis data.
     */
    public function view(CustomerSession $session): CartView
    {
        $items = $this->load($session);
        if ($items === []) {
            return new CartView([], 0, 0, false);
        }

        $ticketTypes = TicketType::query()
            ->withoutGlobalScope('tenant')
            ->with('tenant:id,display_name,status')
            ->whereIn('id', array_column($items, 'ticket_type_id'))
            ->get()
            ->keyBy('id');

        $lines = [];
        $subtotal = 0;
        $totalTickets = 0;
        $blocking = false;

        foreach ($items as $item) {
            $ticketType = $ticketTypes->get($item['ticket_type_id']);
            $line = $this->revalidateLine($item, $ticketType);
            $lines[] = $line;
            $totalTickets += $line->quantity;

            if ($line->available) {
                $subtotal += $line->lineTotal;
            } else {
                $blocking = true;
            }
        }

        return new CartView($lines, $subtotal, $totalTickets, $blocking);
    }

    private function revalidateLine(array $item, ?TicketType $ticketType): CartLine
    {
        $quantity = (int) $item['quantity'];
        $issues = [];
        $available = true;

        $tenantActive = $ticketType !== null
            && $ticketType->tenant !== null
            && $ticketType->tenant->status === 'active';

        if ($ticketType === null || ! $tenantActive) {
            $issues[] = 'ticket_unavailable';
            $available = false;
        }

        $unitPrice = (int) ($ticketType->price ?? 0);

        if ($ticketType !== null && (int) $item['price_at_add'] !== $unitPrice) {
            $issues[] = 'price_changed';
        }

        $lineTotal = $available ? $unitPrice * $quantity : 0;

        return new CartLine(
            lineKey: (string) $item['line_key'],
            ticketTypeId: (int) $item['ticket_type_id'],
            tenantId: (int) $item['tenant_id'],
            destinationName: $ticketType?->tenant->display_name ?? (string) $item['destination_name'],
            ticketTypeName: $ticketType?->name ?? (string) $item['ticket_type_name'],
            visitDate: (string) $item['visit_date'],
            quantity: $quantity,
            unitPrice: $unitPrice,
            priceAtAdd: (int) $item['price_at_add'],
            addons: [],
            addonTotal: 0,
            lineTotal: $lineTotal,
            available: $available,
            issues: $issues,
            note: isset($item['note']) ? (string) $item['note'] : null,
        );
    }

    private function lineKey(int $ticketTypeId, string $visitDate, ?string $note = null): string
    {
        return substr(hash('sha256', $ticketTypeId.'|'.$visitDate.'|'.($note ?? '')), 0, 24);
    }

    private function load(CustomerSession $session): array
    {
        $raw = Redis::get($this->key($session));
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['items']) || ! is_array($decoded['items'])) {
            return [];
        }
        $items = [];
        foreach ($decoded['items'] as $item) {
            if (is_array($item) && isset($item['line_key'])) {
                $items[(string) $item['line_key']] = $item;
            }
        }

        return $items;
    }

    private function persist(CustomerSession $session, array $items): void
    {
        if ($items === []) {
            Redis::del($this->key($session));

            return;
        }
        $payload = json_encode(['items' => array_values($items)], JSON_THROW_ON_ERROR);
        Redis::setex($this->key($session), self::FALLBACK_TTL_SECONDS, $payload);
    }

    private function key(CustomerSession $session): string
    {
        return self::KEY_PREFIX.$session->getKey();
    }
}