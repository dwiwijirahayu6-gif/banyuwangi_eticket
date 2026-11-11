<?php

namespace App\Modules\Catalog\Services;

use App\Models\TicketType;
use App\Modules\Admin\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final class TicketStockService
{
    public function __construct(
        private AuditLogger $audit
    ) {}

    /**
     * Mengubah stok secara atomik dan mencatat setiap pergerakan.
     * Kunci idempotensi mencegah operasi yang sama dijalankan dua kali.
     */
    public function adjust(
        TicketType $ticket,
        int $quantityDelta,
        string $type,
        string $idempotencyKey,
        ?string $reason = null
    ): TicketType {
        if ($quantityDelta === 0) {
            throw new InvalidArgumentException(
                'Perubahan stok tidak boleh nol.'
            );
        }

        if (trim($idempotencyKey) === '') {
            throw new InvalidArgumentException(
                'Idempotency key wajib diisi.'
            );
        }

        return DB::transaction(function () use (
            $ticket,
            $quantityDelta,
            $type,
            $idempotencyKey,
            $reason
        ): TicketType {
            // Ambil data tiket dan kunci baris selama transaksi.
            $locked = TicketType::query()
                ->withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->where('tenant_id', $ticket->tenant_id)
                ->findOrFail($ticket->id);

            // Periksa apakah idempotency key sudah pernah digunakan.
            $existing = DB::table('ticket_stock_movements')
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                // Pastikan key yang sama tidak dipakai untuk operasi berbeda.
                if (
                    (int) $existing->ticket_type_id !== $locked->id
                    || (int) $existing->quantity_delta !== $quantityDelta
                    || $existing->type !== $type
                ) {
                    throw new LogicException(
                        'Idempotency key sudah digunakan untuk operasi berbeda.'
                    );
                }

                return $locked;
            }

            // Hitung stok baru setelah tiket berhasil dikunci.
            $newStock = $locked->stock_qty + $quantityDelta;

            // Cegah stok menjadi negatif.
            if ($newStock < 0) {
                throw new InvalidArgumentException(
                    'Stok tiket tidak mencukupi. Stok tidak boleh negatif.'
                );
            }

            // Catat pergerakan stok.
            $inserted = DB::table('ticket_stock_movements')
                ->insertOrIgnore([
                    'tenant_id' => $locked->tenant_id,
                    'ticket_type_id' => $locked->id,
                    'idempotency_key' => $idempotencyKey,
                    'type' => $type,
                    'quantity_delta' => $quantityDelta,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            // Jika terjadi duplikasi key, pastikan operasi memang sama.
            if ($inserted === 0) {
                $existing = DB::table('ticket_stock_movements')
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if (
                    $existing === null
                    || (int) $existing->ticket_type_id !== $locked->id
                    || (int) $existing->quantity_delta !== $quantityDelta
                    || $existing->type !== $type
                ) {
                    throw new LogicException(
                        'Idempotency key sudah digunakan untuk operasi berbeda.'
                    );
                }

                return $locked;
            }

            // Simpan stok baru.
            $locked->forceFill([
                'stock_qty' => $newStock,
            ])->save();

            // Catat aktivitas perubahan stok.
            $this->audit->record(
                'ticket_type',
                $locked->id,
                'stock_' . $type,
                [
                    'stock_qty' => $newStock - $quantityDelta,
                ],
                [
                    'delta' => $quantityDelta,
                    'reason' => $reason,
                    'stock_qty' => $newStock,
                ],
                $locked->tenant_id
            );

            return $locked;
        });
    }

    /**
     * Mengatur stok ke jumlah tertentu.
     */
    public function setQuantity(
        TicketType $ticket,
        int $targetQuantity,
        string $idempotencyKey,
        ?string $reason = null
    ): TicketType {
        if ($targetQuantity < 0) {
            throw new InvalidArgumentException(
                'Stok tidak boleh negatif.'
            );
        }

        if (trim($idempotencyKey) === '') {
            throw new InvalidArgumentException(
                'Idempotency key wajib diisi.'
            );
        }

        return DB::transaction(function () use (
            $ticket,
            $targetQuantity,
            $idempotencyKey,
            $reason
        ): TicketType {
            $locked = TicketType::query()
                ->withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->where('tenant_id', $ticket->tenant_id)
                ->findOrFail($ticket->id);

            $delta = $targetQuantity - $locked->stock_qty;

            if ($delta === 0) {
                return $locked;
            }

            return $this->adjust(
                $locked,
                $delta,
                'manual_adjustment',
                $idempotencyKey,
                $reason
            );
        });
    }

    /**
     * Sakelar ketersediaan dengan row lock dan audit.
     */
    public function toggleAvailability(TicketType $ticket): TicketType
    {
        return DB::transaction(function () use ($ticket): TicketType {
            $locked = TicketType::query()
                ->withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->where('tenant_id', $ticket->tenant_id)
                ->findOrFail($ticket->id);

            $before = $locked->is_available;

            $locked->forceFill([
                'is_available' => ! $before,
            ])->save();

            $this->audit->record(
                'ticket_type',
                $locked->id,
                'toggle_availability',
                [
                    'is_available' => $before,
                ],
                [
                    'is_available' => $locked->is_available,
                ],
                $locked->tenant_id
            );

            return $locked;
        });
    }
}
