<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Kedaluwarsakan pesanan yang melewati batas waktu pembayaran';

    public function handle(BookingService $service): int
    {
        $this->info($service->expireOverdue().' pesanan dikedaluwarsakan.');

        return self::SUCCESS;
    }
}