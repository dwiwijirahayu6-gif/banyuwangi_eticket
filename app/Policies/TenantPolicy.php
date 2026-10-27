<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;

class TenantPolicy
{
    // Super Admin bisa melihat semua tenant
    public function viewAny(User $user): bool
    {
        return $user->role === 'super_admin'; 
    }

    // Super Admin bisa membuat tenant baru
    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    // Super Admin bisa update tenant apa saja
    public function update(User $user, Tenant $tenant): bool
    {
        return $user->role === 'super_admin';
    }

    public function scheduleCommission(User $user, Tenant $tenant): bool
    {
        return $user->role === 'super_admin';
    }

    public function manageBank(User $user, Tenant $tenant): bool
    {
        return $user->role === 'super_admin';
    }

    public function assignRole(User $user, Tenant $tenant): bool
    {
        return $user->role === 'super_admin';
    }
}