<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Ticket;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public const PAYMENT_WINDOW_MINUTES = 30;
    public const MAX_TICKETS_PER_BOOKING = 10;
    public const MAX_DAYS_AHEAD = 30;
    public const TIMEZONE = 'Asia/Jakarta';

    /**
     * Checkout: buat pesanan (menunggu pembayaran) beserta item dan tagihan QRIS.
     * Harga selalu diambil dari database, bukan dari input pengguna.
     *
     * @param  array{visit_date:string, customer_name:string, customer_email:string, customer_phone:string, quantities:array<int|string,mixed>}  $data
     */
    public function checkout(Destination $destination, array $data): Booking
    {
        $this->assertVisitDateIsValid($data['visit_date']);

        $quantities = collect($data['quantities'] ?? [])
            ->map(fn ($qty) => (int) $qty)
            ->filter(fn (int $qty) => $qty > 0);

        if ($quantities->isEmpty()) {
            throw new DomainException('Pilih minimal satu tiket.');
        }

        if ($quantities->sum() > self::MAX_TICKETS_PER_BOOKING) {
            throw new DomainException('Maksimal '.self::MAX_TICKETS_PER_BOOKING.' tiket per pesanan.');
        }

        $ticketTypes = $destination->ticketTypes()
            ->whereIn('id', $quantities->keys()->all())
            ->get()
            ->keyBy('id');

        if ($ticketTypes->count() !== $quantities->count()) {
            throw new DomainException('Jenis tiket yang dipilih tidak valid untuk destinasi ini.');
        }

        return DB::transaction(function () use ($destination, $data, $quantities, $ticketTypes) {
            $lines = $quantities->map(function (int $qty, $typeId) use ($ticketTypes) {
                $price = (int) round((float) $ticketTypes[$typeId]->price);

                return ['type' => $ticketTypes[$typeId], 'qty' => $qty, 'price' => $price, 'subtotal' => $price * $qty];
            });

            $total = $lines->sum('subtotal');

            $booking = Booking::create([
                'booking_code' => $this->generateBookingCode(),
                'customer_name' => $data['customer_name'],
                'customer_email' => strtolower($data['customer_email']),
                'customer_phone' => $data['customer_phone'],
                'visit_date' => $data['visit_date'],
                'total_amount' => $total,
                'status' => 'pending_payment',
                'expires_at' => now()->addMinutes(self::PAYMENT_WINDOW_MINUTES),
            ]);

            foreach ($lines as $line) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'tenant_id' => $destination->tenant_id,
                    'ticket_type_id' => $line['type']->id,
                    'quantity' => $line['qty'],
                    'unit_price_snapshot' => $line['price'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            $reference = 'PAY-'.Str::upper(Str::random(12));

            Payment::create([
                'booking_id' => $booking->id,
                'payment_reference' => $reference,
                // Payload simulasi. Ganti dengan payload QRIS dari payment gateway saat integrasi nyata.
                'qris_payload' => "QRIS-SIMULASI|{$reference}|{$booking->booking_code}|{$total}",
                'gross_amount' => $total,
                'status' => 'pending',
            ]);

            return $booking;
        });
    }

    /**
     * Tandai pesanan lunas dan terbitkan tiket. Aman dipanggil berulang (idempoten),
     * sehingga cocok dipakai juga oleh webhook payment gateway nantinya.
     */
    public function markAsPaid(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($booking->status === 'paid') {
                return $booking;
            }

            $this->expireIfNeeded($booking);

            if ($booking->status !== 'pending_payment') {
                throw new DomainException('Pesanan ini sudah '.strtolower($booking->statusLabel()).' dan tidak dapat dibayar.');
            }

            $booking->payment()->update(['status' => 'settlement']);
            $booking->update(['status' => 'paid', 'paid_at' => now()]);

            $this->issueTickets($booking);

            return $booking->refresh();
        });
    }

    /**
     * Batalkan pesanan sesuai aturan pada Booking::cancellationBlockedReason().
     */
    public function cancel(Booking $booking, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            $this->expireIfNeeded($booking);

            if ($blocked = $booking->cancellationBlockedReason()) {
                throw new DomainException($blocked);
            }

            $wasPaid = $booking->status === 'paid';

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason ? Str::limit(trim($reason), 250, '') : null,
            ]);

            $booking->tickets()->update(['status' => 'cancelled']);

            // Tagihan yang belum dibayar ditutup. Pengembalian dana untuk pesanan lunas
            // belum diotomatisasi dan diproses manual oleh pengelola.
            if (! $wasPaid) {
                $booking->payment()->where('status', 'pending')->update(['status' => 'failed']);
            }

            return $booking->refresh();
        });
    }

    /**
     * Ubah pesanan yang lewat batas waktu bayar menjadi kedaluwarsa.
     */
    public function expireIfNeeded(Booking $booking): bool
    {
        if ($booking->status !== 'pending_payment' || ! $booking->expires_at || $booking->expires_at->isFuture()) {
            return false;
        }

        $booking->update(['status' => 'expired']);
        $booking->payment()->where('status', 'pending')->update(['status' => 'expire']);

        return true;
    }

    /**
     * Kedaluwarsakan semua pesanan yang melewati batas waktu bayar. Mengembalikan jumlahnya.
     */
    public function expireOverdue(): int
    {
        $count = 0;

        Booking::where('status', 'pending_payment')
            ->where('expires_at', '<=', now())
            ->each(function (Booking $booking) use (&$count) {
                if ($this->expireIfNeeded($booking)) {
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Akses pesanan disimpan di session setelah checkout atau setelah lookup
     * (kode pesanan + email), karena tidak ada akun pelanggan.
     */
    public function grantAccess(Booking $booking): void
    {
        session()->push('booking_access', $booking->booking_code);
    }

    public function hasAccess(Booking $booking): bool
    {
        return in_array($booking->booking_code, session('booking_access', []), true);
    }

    private function issueTickets(Booking $booking): void
    {
        $number = 1;

        foreach ($booking->items()->get() as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                Ticket::create([
                    'booking_item_id' => $item->id,
                    'booking_id' => $booking->id,
                    'tenant_id' => $item->tenant_id,
                    'ticket_code' => $booking->booking_code.'-'.str_pad((string) $number++, 2, '0', STR_PAD_LEFT),
                    'status' => 'issued',
                    'visit_date' => $booking->visit_date,
                ]);
            }
        }
    }

    private function assertVisitDateIsValid(string $date): void
    {
        $today = Carbon::now(self::TIMEZONE)->startOfDay();
        $visit = Carbon::createFromFormat('Y-m-d', $date, self::TIMEZONE)->startOfDay();

        if ($visit->lt($today)) {
            throw new DomainException('Tanggal kunjungan tidak boleh sebelum hari ini.');
        }

        if ($visit->gt($today->copy()->addDays(self::MAX_DAYS_AHEAD))) {
            throw new DomainException('Tanggal kunjungan maksimal '.self::MAX_DAYS_AHEAD.' hari dari sekarang.');
        }
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'TIX-BWI-'.Carbon::now(self::TIMEZONE)->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}