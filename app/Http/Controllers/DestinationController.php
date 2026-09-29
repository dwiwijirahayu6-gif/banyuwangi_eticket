<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\BookingService;

class DestinationController extends Controller
{
    // Halaman "Informasi Tiket": harga, jam buka, dan ketentuan semua destinasi.
    public function index()
    {
        $destinations = Destination::with(['tenant', 'ticketTypes'])->orderBy('name')->get();

        return view('destinations.index', [
            'destinations' => $destinations,
            'paymentWindow' => BookingService::PAYMENT_WINDOW_MINUTES,
            'maxTickets' => BookingService::MAX_TICKETS_PER_BOOKING,
        ]);
    }

    // Detail destinasi sekaligus formulir checkout.
    public function show(Destination $destination)
    {
        $destination->load(['tenant', 'ticketTypes']);

        return view('destinations.show', [
            'destination' => $destination,
            'minDate' => now(BookingService::TIMEZONE)->toDateString(),
            'maxDate' => now(BookingService::TIMEZONE)->addDays(BookingService::MAX_DAYS_AHEAD)->toDateString(),
            'maxTickets' => BookingService::MAX_TICKETS_PER_BOOKING,
            'paymentWindow' => BookingService::PAYMENT_WINDOW_MINUTES,
        ]);
    }
}