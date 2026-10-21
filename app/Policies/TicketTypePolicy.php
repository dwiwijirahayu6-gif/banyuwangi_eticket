<?php

namespace App\Policies;

use App\Models\TicketType;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class TicketTypePolicy
{
    private function ownedByActiveTenant(TicketType $ticketType): bool
    {
        return $ticketType->tenant_id === app(TenantContext::class)->idOrNull();
    }

    public function view(User $user, TicketType $ticketType): bool
    {
        return $this->ownedByActiveTenant($ticketType);
    }

    public function update(User $user, TicketType $ticketType): bool
    {
        return $this->ownedByActiveTenant($ticketType);
    }

    public function delete(User $user, TicketType $ticketType): bool
    {
        return $this->ownedByActiveTenant($ticketType);
    }
}