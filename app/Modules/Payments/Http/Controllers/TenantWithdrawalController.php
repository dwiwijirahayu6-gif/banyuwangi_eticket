<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\Withdrawal;
use App\Services\LedgerService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantWithdrawalController extends Controller
{
    public function __construct(private LedgerService $ledgerService) {}

    public function index(Request $request, Tenant $tenant): View
    {
        $balanceInfo = $this->ledgerService->getBalanceInfo($tenant->id);

        $bankAccounts = TenantBankAccount::where('tenant_id', $tenant->id)->get();

        $withdrawals = Withdrawal::where('tenant_id', $tenant->id)
            ->latest('id')
            ->paginate(10, ['*'], 'withdrawals_page')
            ->withQueryString();

        $ledgerEntries = LedgerEntry::where('tenant_id', $tenant->id)
            ->with('bookingItem.ticketType')
            ->latest('id')
            ->paginate(15, ['*'], 'ledger_page')
            ->withQueryString();

        return view('payments::tenant.withdrawals.index', [
            'tenant' => $tenant,
            'balanceInfo' => $balanceInfo,
            'bankAccounts' => $bankAccounts,
            'withdrawals' => $withdrawals,
            'ledgerEntries' => $ledgerEntries,
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'bank_account_id' => ['required', 'integer'],
        ], [
            'amount.required' => 'Nominal penarikan wajib diisi.',
            'amount.min' => 'Nominal penarikan minimal Rp 10.000.',
            'bank_account_id.required' => 'Pilih rekening bank tujuan pencairan.',
        ]);

        $bankAccount = TenantBankAccount::where('tenant_id', $tenant->id)
            ->whereKey($data['bank_account_id'])
            ->first();

        if (! $bankAccount) {
            return back()->withInput()->with('error', 'Rekening bank tidak ditemukan atau bukan milik tenant ini.');
        }

        try {
            $this->ledgerService->requestWithdrawal($tenant, (float) $data['amount'], $bankAccount);

            return redirect()->route('tenant.saldo-penarikan', ['tenant' => $tenant->code])
                ->with('success', 'Pengajuan penarikan dana berhasil dikirim dan saldo sedang dikunci menunggu verifikasi Admin Pusat.');
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
