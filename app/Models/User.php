<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
        public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Apakah user memegang role tertentu (kontrak interim Modul 2).
     * Mapping: 'admin' → 'super_admin', 'tenant' → 'tenant_admin'.
     */
    public function hasRole(string $role): bool
    {
        $mapped = match ($role) {
            'admin' => 'super_admin',
            'tenant' => 'tenant_admin',
            default => $role,
        };

        return $this->role === $mapped && $this->isActive();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTenantOperator(): bool
    {
        return $this->hasRole('tenant');
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }
}