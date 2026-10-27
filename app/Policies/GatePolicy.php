<?php

namespace App\Policies;

use App\Models\Gate;
use App\Models\User;

class GatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, Gate $gate): bool
    {
        return $user->role === 'super_admin';
    }

    public function rotateQr(User $user, Gate $gate): bool
    {
        return $user->role === 'super_admin';
    }
}