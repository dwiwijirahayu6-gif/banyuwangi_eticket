<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\TicketType;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CountTenantTickets implements ShouldQueue
{
    use Queueable;

    public ?int $result = null;

    /**
     * Job tenant-scoped: membawa $tenantId (bukan model context statis),
     * membentuk context di handle(), lalu MEMBERSIHKANNYA di finally.
     */
    public function __construct(public int $tenantId) {}

    public function handle(TenantContext $context): void
    {
        $tenant = Tenant::query()->findOrFail($this->tenantId);
        $context->set($tenant);

        try {
            // Query menggunakan global scope tenant aktif
            $this->result = TicketType::query()->count();
        } finally {
            $context->clear();
        }
    }
}