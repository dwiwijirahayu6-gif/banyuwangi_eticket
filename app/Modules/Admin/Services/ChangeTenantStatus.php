<?php

namespace App\Modules\Admin\Services;

use App\Models\Tenant;
use DomainException;
use Illuminate\Support\Facades\DB;

final class ChangeTenantStatus
{
    public const ALLOWED = ['active', 'inactive', 'suspended'];

    public function __construct(private AuditLogger $audit) {}

    public function handle(Tenant $tenant, string $status): Tenant
    {
        if (! in_array($status, self::ALLOWED, true)) {
            throw new DomainException('Status tenant tidak dikenal.');
        }

        return DB::transaction(function () use ($tenant, $status): Tenant {
            $locked = Tenant::query()->lockForUpdate()->findOrFail($tenant->id);
            $before = $locked->status;

            if ($before === $status) {
                return $locked;
            }

            $locked->update(['status' => $status]);

            $actionName = match ($status) {
                'active' => 'activated',
                'suspended' => 'suspended',
                default => 'deactivated',
            };

            $this->audit->record(
                'tenant',
                $locked->id,
                $actionName,
                ['status' => $before],
                ['status' => $status],
                $locked->id
            );

            return $locked;
        });
    }
}
