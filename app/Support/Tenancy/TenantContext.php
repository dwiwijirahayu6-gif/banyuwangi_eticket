<?php

namespace App\Support\Tenancy;

use RuntimeException;

class TenantContext
{
    protected ?int $tenantId = null;

    public function set(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function clear(): void
    {
        $this->tenantId = null;
    }

    public function has(): bool
    {
        return $this->tenantId !== null;
    }

    public function id(): int
    {
        if (! $this->has()) {
            throw new RuntimeException('Tenant context belum tersedia.');
        }

        return $this->tenantId;
    }

    public function idOrNull(): ?int
    {
        return $this->tenantId;
    }
}
