<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommissionScheme;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\UserTenantRole;
use App\Modules\Admin\Http\Requests\StoreTenantRequest;
use App\Modules\Admin\Services\CreateTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class AdminTenantController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CreateTenant $createTenant) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Tenant::class);

        $query = Tenant::with(['destination', 'users' => function ($q) {
            $q->where('role', 'tenant_admin');
        }])->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('destination', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%");
                    })
                    ->orWhereHas('users', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), ['active', 'inactive', 'suspended'], true)) {
            $query->where('status', $request->input('status'));
        }

        $tenants = $query->paginate(15)->withQueryString();

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
        $tenant = $this->createTenant->handle($data, $request->file('image'));

        return redirect()->route('admin.tenants.edit', $tenant)
            ->with('status', "Destinasi '{$tenant->destination?->name}' dan akun Admin Tenant berhasil didaftarkan.");
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        $tenant->load('destination');
        $members = UserTenantRole::with('user')->where('tenant_id', $tenant->id)->get();
        $adminUsers = $tenant->users()->where('role', 'tenant_admin')->get();

        $commissions = CommissionScheme::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('valid_from')
            ->get();

        $activeCommission = $commissions->whereNull('valid_to')->first();

        $bankAccounts = TenantBankAccount::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('is_primary')
            ->get();

        return view('admin::tenants.edit', compact('tenant', 'members', 'adminUsers', 'commissions', 'activeCommission', 'bankAccounts'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'destination_name'  => ['required', 'string', 'max:255'],
            'location'          => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string'],
            'operating_hours'   => ['nullable', 'string', 'max:255'],
            'facilities'        => ['nullable', 'string'],
            'safety_guidelines' => ['nullable', 'string'],
            'image'             => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $destination = $tenant->destination;
        if (! $destination) {
            $destination = new Destination();
            $destination->tenant_id = $tenant->id;
            $destination->slug = Str::slug($validated['destination_name']);
        }

        if ($request->hasFile('image')) {
            $destination->image = 'storage/' . $request->file('image')->store('destinations', 'public');
        }

        $destination->name = $validated['destination_name'];
        $destination->location = $validated['location'];
        $destination->description = $validated['description'];
        if ($request->filled('operating_hours')) {
            $destination->operating_hours = $validated['operating_hours'];
        }
        $destination->facilities = $validated['facilities'] ?? null;
        $destination->safety_guidelines = $validated['safety_guidelines'] ?? null;
        $destination->save();

        $tenant->update([
            'name' => 'Pengelola ' . $validated['destination_name'],
        ]);

        return redirect()->route('admin.tenants.edit', $tenant)
            ->with('status', 'Informasi destinasi wisata berhasil diperbarui.');
    }
}
