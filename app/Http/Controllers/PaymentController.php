<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use DomainException;

class PaymentController extends Controller
{
    public function show(Booking $booking, BookingService $service)
    {
        if (! $service->hasAccess($booking)) {
            return $this->denied();
        }

        $service->expireIfNeeded($booking);
        $booking->load(['items.ticketType.destination', 'payment']);

        if ($booking->status === 'paid') {
            return redirect()->route('bookings.show', $booking);
        }

        return view('bookings.payment', [
            'booking' => $booking,
            'canSimulate' => app()->environment(['local', 'testing']),
        ]);
    }

    /**
     * Simulasi pembayaran berhasil (hanya di environment local/testing).
     * Untuk produksi, panggil BookingService::markAsPaid() dari webhook payment gateway
     * setelah memverifikasi signature notifikasinya.
     */
    public function simulate(Booking $booking, BookingService $service)
    {
        if (! $service->hasAccess($booking)) {
            return $this->denied();
        }

        abort_unless(app()->environment(['local', 'testing']), 404);

        try {
            $service->markAsPaid($booking);
        } catch (DomainException $e) {
            return redirect()->route('payment.show', $booking)->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Pembayaran berhasil! Tiket Anda sudah terbit.');
    }

    private function denied()
    {
        return redirect()->route('bookings.lookup')
            ->with('error', 'Masukkan kode pesanan dan email untuk mengakses pesanan Anda.');
    }
}