<?php

namespace App\Modules\Ordering\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\CustomerSession;
use App\Modules\Ordering\Data\CheckoutResult;
use App\Modules\Ordering\Exceptions\CheckoutException;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CheckoutService
{
    public function __construct(private CartService $cart) {}

    public function checkout(
        CustomerSession $session, 
        string $idempotencyKey, 
        ?CarbonInterface $scheduledAt = null, 
        ?array $customerDetails = null
    ): CheckoutResult {
        
        // 1. Cek Idempotency (Mencegah double klik)
        // Kita gunakan idempotencyKey sebagai booking_code sementara
        $existing = Booking::query()->where('booking_code', $idempotencyKey)->first();
        if ($existing !== null) {
            return new CheckoutResult($existing, null, isReplay: true);
        }

        // 2. Validasi Keranjang
        $view = $this->cart->view($session);
        if ($view->isEmpty()) {
            throw CheckoutException::emptyCart();
        }
        if ($view->hasBlockingIssues) {
            throw CheckoutException::cartNotOrderable();
        }

        // 3. Mulai Transaksi Database
        return DB::transaction(function () use ($session, $idempotencyKey, $scheduledAt, $customerDetails, $view): CheckoutResult {
            
            $visitDate = $scheduledAt ? $scheduledAt->toDateString() : now()->toDateString();
            
            // Buat Header Booking (Pesanan)
            $booking = new Booking();
            $booking->booking_code = 'ORD-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
            $booking->customer_name = $customerDetails['name'] ?? 'Pelanggan';
            $booking->customer_email = $customerDetails['email'] ?? 'guest@example.com';
            $booking->customer_phone = $customerDetails['phone'] ?? '08000000000';
            $booking->total_amount = $view->grandTotal;
            $booking->status = 'pending_payment';
            $booking->visit_date = $visitDate;
            $booking->expires_at = now()->addMinutes(30); // Batas bayar 30 menit
            $booking->save();

            // Simpan Rincian Item (BookingItem)
            foreach ($view->lines as $line) {
                $item = new BookingItem();
                $item->booking_id = $booking->id;
                $item->tenant_id = $line->tenantId;
                $item->ticket_type_id = $line->ticketTypeId;
                $item->visit_slot_id = $line->visitSlotId; 
                $item->quantity = $line->quantity;
                $item->unit_price_snapshot = $line->unitPrice;
                $item->subtotal = $line->lineTotal;
                $item->save();
            }

            // Kosongkan keranjang
            $this->cart->clear($session);

            // Buat token pelacakan
            $trackingToken = Str::random(32);

            return new CheckoutResult($booking, $trackingToken);
        });
    }
}