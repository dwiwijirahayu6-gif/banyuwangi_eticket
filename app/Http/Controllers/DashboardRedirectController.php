<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\UserTenantRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Jika Super Admin, arahkan ke Admin Dashboard
        if ($user?->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        // 2. Jika Pengelola Destinasi (tenant_admin), cari tenant-nya
        if ($user?->role === 'tenant_admin') {
            $tenantRole = UserTenantRole::where('user_id', $user->id)->first();

            if ($tenantRole && $tenantRole->tenant) {
                return redirect()->route('tenant.dashboard', ['tenant' => $tenantRole->tenant->code ?? $tenantRole->tenant->id]);
            }

            if ($user->tenant_id && $user->tenant) {
                return redirect()->route('tenant.dashboard', ['tenant' => $user->tenant->code ?? $user->tenant->id]);
            }

            $firstTenant = Tenant::where('status', 'active')->first();
            if ($firstTenant) {
                $user->forceFill(['tenant_id' => $firstTenant->id])->save();

                return redirect()->route('tenant.dashboard', ['tenant' => $firstTenant->code ?? $firstTenant->id]);
            }
        }

        // 3. Fallback jika tidak punya role khusus
        return redirect('/');
    }
}
