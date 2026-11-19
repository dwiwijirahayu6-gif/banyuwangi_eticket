<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\VisitSlot;
use App\Modules\Catalog\Services\VisitSlotQuotaService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantVisitSlotController extends Controller
{
    public function __construct(private VisitSlotQuotaService $quotaService) {}

    public function index(Request $request, Tenant $tenant): View
    {
        $destination = $tenant->destinations()->first();

        $slots = VisitSlot::where('tenant_id', $tenant->id)
            ->when($destination, fn ($q) => $q->where('destination_id', $destination->id))
            ->orderBy('visit_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('catalog::tenant.visit-slots', [
            'tenant' => $tenant,
            'destination' => $destination,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $destination = $tenant->destinations()->firstOrFail();

        $data = $request->validate([
            'visit_date' => ['required', 'date'],
            'quota_capacity' => ['required', 'integer', 'min:1', 'max:50000'],
        ], [
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'quota_capacity.required' => 'Kapasitas kuota wajib diisi.',
            'quota_capacity.min' => 'Kapasitas kuota minimal 1 pengunjung.',
        ]);

        try {
            $this->quotaService->setQuota($tenant, $destination, $data['visit_date'], (int) $data['quota_capacity']);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.jadwal-kuota', ['tenant' => $tenant->code])
            ->with('success', 'Jadwal dan kuota kunjungan berhasil disimpan.');
    }

    public function update(Request $request, Tenant $tenant, VisitSlot $visitSlot): RedirectResponse
    {
        abort_unless($visitSlot->tenant_id === $tenant->id, 403);
        $destination = $tenant->destinations()->firstOrFail();

        $data = $request->validate([
            'quota_capacity' => ['required', 'integer', 'min:1', 'max:50000'],
        ], [
            'quota_capacity.required' => 'Kapasitas kuota wajib diisi.',
            'quota_capacity.min' => 'Kapasitas kuota minimal 1 pengunjung.',
        ]);

        try {
            $this->quotaService->setQuota($tenant, $destination, $visitSlot->visit_date->toDateString(), (int) $data['quota_capacity']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.jadwal-kuota', ['tenant' => $tenant->code])
            ->with('success', 'Kapasitas kuota berhasil diperbarui.');
    }

    public function toggle(Tenant $tenant, VisitSlot $visitSlot): RedirectResponse
    {
        abort_unless($visitSlot->tenant_id === $tenant->id, 403);

        $this->quotaService->toggleStatus($visitSlot);

        $statusText = $visitSlot->is_active ? 'dibuka kembali' : 'ditutup sementara';

        return redirect()->route('tenant.jadwal-kuota', ['tenant' => $tenant->code])
            ->with('success', "Jadwal kunjungan tanggal {$visitSlot->visit_date->format('d/m/Y')} berhasil {$statusText}.");
    }
}
