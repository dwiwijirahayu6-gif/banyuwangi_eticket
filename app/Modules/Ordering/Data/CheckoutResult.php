<?php

namespace App\Modules\Ordering\Data;

use App\Models\Order; // Sesuaikan jika temanmu pakai model Booking

final readonly class CheckoutResult
{
    public function __construct(
        public Order $order,
        public string $trackingToken,
        public bool $isReplay = false,
    ) {}
}