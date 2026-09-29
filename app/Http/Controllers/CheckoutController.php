<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\BookingService;
use DomainException;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function store(Request $request, Destination $destination, BookingService $service)
    {
        $data = $request->validate([
            'visit_date' => ['required', 'date_format:Y-m-d'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'email', 'max:150'],
            'customer_phone' => ['required', 'regex:/^(\+62|62|0)8\d{8,12}$/'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:0', 'max:'.BookingService::MAX_TICKETS_PER_BOOKING],
        ], [
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_date.date_format' => 'Format tanggal kunjungan tidak valid.',
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_email.required' => 'Email wajib diisi.',
            'customer_email.email' => 'Format email tidak valid.',
            'customer_phone.required' => 'Nomor HP wajib diisi.',
            'customer_phone.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
            'quantities.required' => 'Pilih minimal satu tiket.',
            'quantities.*.integer' => 'Jumlah tiket harus berupa angka.',
            'quantities.*.max' => 'Maksimal '.BookingService::MAX_TICKETS_PER_BOOKING.' tiket per jenis.',
        ]);

        try {
            $booking = $service->checkout($destination, $data);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $service->grantAccess($booking);

        return redirect()->route('payment.show', $booking)
            ->with('success', 'Pesanan berhasil dibuat. Selesaikan pembayaran sebelum waktu habis.');
    }
}