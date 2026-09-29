<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use DomainException;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // Form cek pesanan / pembatalan tiket (kode pesanan + email).
    public function lookupForm()
    {
        return view('bookings.lookup', [
            'cutoffNote' => 'Tiket yang sudah dibayar dapat dibatalkan paling lambat H-1 sebelum tanggal kunjungan.',
        ]);
    }

    public function lookup(Request $request, BookingService $service)
    {
        $data = $request->validate([
            'booking_code' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150'],
        ], [
            'booking_code.required' => 'Kode pesanan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $booking = Booking::where('booking_code', strtoupper(trim($data['booking_code'])))
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($data['email'])])
            ->first();

        if (! $booking) {
            return back()->withInput()
                ->with('error', 'Pesanan tidak ditemukan. Periksa kembali kode pesanan dan email Anda.');
        }

        $service->grantAccess($booking);

        return redirect()->route('bookings.show', $booking);
    }

    public function show(Booking $booking, BookingService $service)
    {
        if (! $service->hasAccess($booking)) {
            return redirect()->route('bookings.lookup')
                ->with('error', 'Masukkan kode pesanan dan email untuk mengakses pesanan Anda.');
        }

        $service->expireIfNeeded($booking);
        $booking->load(['items.ticketType.destination', 'tickets', 'payment']);

        return view('bookings.show', [
            'booking' => $booking,
            'blockedReason' => $booking->cancellationBlockedReason(),
        ]);
    }

    public function cancel(Request $request, Booking $booking, BookingService $service)
    {
        if (! $service->hasAccess($booking)) {
            return redirect()->route('bookings.lookup')
                ->with('error', 'Masukkan kode pesanan dan email untuk mengakses pesanan Anda.');
        }

        $data = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:250'],
        ]);

        try {
            $wasPaid = $booking->status === 'paid';
            $service->cancel($booking, $data['cancel_reason'] ?? null);
        } catch (DomainException $e) {
            return redirect()->route('bookings.show', $booking)->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)->with(
            'success',
            $wasPaid
                ? 'Pesanan dibatalkan. Pengembalian dana akan diproses oleh pengelola destinasi.'
                : 'Pesanan berhasil dibatalkan.'
        );
    }
}