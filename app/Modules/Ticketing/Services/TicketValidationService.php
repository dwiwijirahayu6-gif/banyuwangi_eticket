<?php

namespace App\Modules\Ticketing\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Ticket;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TicketValidationService
{
    /**
     * Cari tiket berdasarkan kode booking atau kode tiket untuk tenant tertentu.
     *
     * @return Collection<int, Ticket>
     */
    public function search(int $tenantId, string $code): Collection
    {
        $code = trim($code);
        if ($code === '') {
            return new Collection();
        }

        // Cari berdasarkan kode tiket langsung
        $tickets = Ticket::with(['booking', 'bookingItem.ticketType', 'verifiedBy'])
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($code) {
                $query->where('ticket_code', $code)
                    ->orWhereHas('booking', function ($q) use ($code) {
                        $q->where('booking_code', $code);
                    });
            })
            ->get();

        return $tickets;
    }

    /**
     * Verifikasi masuk tiket individual oleh petugas tenant.
     */
    public function verifyEntrance(int $tenantId, int $ticketId, int $verifiedByUserId): Ticket
    {
        return DB::transaction(function () use ($tenantId, $ticketId, $verifiedByUserId) {
            /** @var Ticket $ticket */
            $ticket = Ticket::with(['booking', 'bookingItem.ticketType'])
                ->where('tenant_id', $tenantId)
                ->whereKey($ticketId)
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                throw new DomainException('Tiket tidak ditemukan atau bukan milik destinasi ini.');
            }

            if ($ticket->booking->status !== 'paid') {
                throw new DomainException(
                    'Tiket belum dapat diverifikasi karena pesanan belum lunas (Status: '
                    .$ticket->booking->statusLabel().').'
                );
            }

            if ($ticket->status === 'checked_in') {
                $checkedTime = $ticket->updated_at ? $ticket->updated_at->format('d/m/Y H:i') : '-';
                $officerName = $ticket->verifiedBy?->name ?? 'Petugas';
                throw new DomainException(
                    "Tiket ini sudah digunakan pada {$checkedTime} WIB oleh {$officerName}."
                );
            }

            if ($ticket->status !== 'issued') {
                throw new DomainException(
                    'Tiket tidak valid atau berstatus: '.$ticket->status
                );
            }

            $ticket->status = 'checked_in';
            $ticket->verified_by_user_id = $verifiedByUserId;
            $ticket->save();

            // Cek apakah semua tiket dalam booking sudah digunakan, jika ya update booking jadi completed
            $booking = $ticket->booking;
            $remainingIssued = Ticket::where('booking_id', $booking->id)
                ->where('status', 'issued')
                ->exists();

            if (! $remainingIssued && $booking->status === 'paid') {
                $booking->update(['status' => 'completed']);
            }

            // Catat audit log jika tabel audit_logs tersedia
            if (class_exists(AuditLog::class)) {
                try {
                    AuditLog::create([
                        'user_id' => $verifiedByUserId,
                        'action' => 'ticket_verified',
                        'entity_type' => 'ticket',
                        'ip_address' => request()->ip(),
                    ]);
                } catch (\Throwable $e) {
                    // Jangan gagalkan verifikasi tiket jika audit log gagal
                }
            }

            return $ticket->load(['booking', 'bookingItem.ticketType', 'verifiedBy']);
        });
    }
}
