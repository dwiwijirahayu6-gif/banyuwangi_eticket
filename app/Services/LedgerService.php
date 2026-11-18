<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CommissionScheme;
use App\Models\LedgerEntry;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\Withdrawal;
use DomainException;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Catat penjualan ke ledger secara idempoten saat booking lunas.
     */
    public function recordBookingSales(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            foreach ($booking->items as $item) {
                // Idempotensi: jangan duplikasi entri
                $exists = LedgerEntry::where('booking_item_id', $item->id)->exists();
                if ($exists) {
                    continue;
                }

                $commissionRate = $this->getCommissionRateForTenant($item->tenant_id);
                $commissionAmount = round($item->subtotal * ($commissionRate / 100), 4);
                $netAmount = max(0, round($item->subtotal - $commissionAmount, 4));

                $lastBalance = (float) (LedgerEntry::where('tenant_id', $item->tenant_id)
                    ->lockForUpdate()
                    ->latest('id')
                    ->value('balance_after') ?? 0);

                $newBalance = $lastBalance + $netAmount;

                LedgerEntry::create([
                    'tenant_id' => $item->tenant_id,
                    'booking_item_id' => $item->id,
                    'withdrawal_id' => null,
                    'transaction_type' => 'credit_sale',
                    'amount' => $netAmount,
                    'balance_after' => $newBalance,
                ]);
            }
        });
    }

    /**
     * Ambil informasi saldo tenant (total, terkunci, tersedia).
     *
     * @return array{current_balance: float, pending_withdrawals: float, available_balance: float}
     */
    public function getBalanceInfo(int $tenantId): array
    {
        $currentBalance = (float) (LedgerEntry::where('tenant_id', $tenantId)
            ->latest('id')
            ->value('balance_after') ?? 0);

        $pendingWithdrawals = (float) (Withdrawal::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->sum('amount') ?? 0);

        $availableBalance = max(0.0, $currentBalance - $pendingWithdrawals);

        return [
            'current_balance' => $currentBalance,
            'pending_withdrawals' => $pendingWithdrawals,
            'available_balance' => $availableBalance,
        ];
    }

    /**
     * Ajukan penarikan dana oleh tenant. Saldo langsung dikunci dengan status pending.
     */
    public function requestWithdrawal(Tenant $tenant, float $amount, TenantBankAccount $bankAccount): Withdrawal
    {
        if ($amount <= 0) {
            throw new DomainException('Nominal penarikan harus lebih dari 0.');
        }

        if ($bankAccount->tenant_id !== $tenant->id) {
            throw new DomainException('Rekening bank tidak valid untuk destinasi ini.');
        }

        return DB::transaction(function () use ($tenant, $amount, $bankAccount) {
            $balanceInfo = $this->getBalanceInfo($tenant->id);

            if ($amount > $balanceInfo['available_balance']) {
                throw new DomainException(
                    'Saldo tersedia tidak mencukupi. Saldo tersedia: Rp '
                    .number_format($balanceInfo['available_balance'], 0, ',', '.')
                );
            }

            $bankInfo = sprintf(
                '%s - %s a.n. %s',
                strtoupper($bankAccount->bank_code),
                '****'.$bankAccount->account_last4,
                $bankAccount->account_holder
            );

            return Withdrawal::create([
                'tenant_id' => $tenant->id,
                'approved_by_user_id' => null,
                'amount' => $amount,
                'status' => 'pending',
                'bank_account_info' => $bankInfo,
            ]);
        });
    }

    /**
     * Dapatkan persentase komisi yang aktif untuk tenant.
     */
    public function getCommissionRateForTenant(int $tenantId): float
    {
        $scheme = CommissionScheme::where('tenant_id', $tenantId)
            ->whereNull('valid_to')
            ->latest('id')
            ->first();

        if ($scheme) {
            return (float) ($scheme->commission_rate ?? $scheme->percentage ?? 5.0);
        }

        $globalScheme = CommissionScheme::whereNull('tenant_id')
            ->whereNull('valid_to')
            ->latest('id')
            ->first();

        if ($globalScheme) {
            return (float) ($globalScheme->commission_rate ?? $globalScheme->percentage ?? 5.0);
        }

        return 5.0; // default 5%
    }
}
