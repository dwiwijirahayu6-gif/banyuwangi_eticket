<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Admin\Services\ChangeTenantStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminTenantStatusController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Tenant $tenant, ChangeTenantStatus $service): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $data = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $service->handle($tenant, $data['status']);

        return redirect()->route('admin.tenants.index')
            ->with('status', "Tenant {$tenant->name} berhasil diubah menjadi {$data['status']}.");
    }
}