<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBankAccount; // Tambahan import
use App\Models\UserTenantRole;
use App\Modules\Admin\Http\Requests\StoreTenantRequest;
use App\Modules\Admin\Services\CreateTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

final class AdminTenantController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CreateTenant $createTenant) {}

    public function index(): View
    {
        $this->authorize('viewAny', Tenant::class);
        $tenants = Tenant::orderBy('id', 'desc')->get();
        return view('admin::tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);
        return view('admin::tenants.create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenant = $this->createTenant->handle($data);

        return redirect()->route('admin.tenants.edit', $tenant)
            ->with('status', 'Destinasi/Tenant berhasil dibuat.');
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);
        
        $members = UserTenantRole::with('user')->where('tenant_id', $tenant->id)->get();
        
        $commissions = CommissionScheme::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('valid_from')
            ->get();
            
        $activeCommission = $commissions->whereNull('valid_to')->first();

        // Ambil data rekening bank
        $bankAccounts = TenantBankAccount::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('is_primary')
            ->get();

        return view('admin::tenants.edit', compact('tenant', 'members', 'commissions', 'activeCommission', 'bankAccounts'));
    }
}