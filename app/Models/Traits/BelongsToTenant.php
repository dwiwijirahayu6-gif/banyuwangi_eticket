<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Support\Tenancy\TenantContext;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Global Scope: Otomatis filter data berdasarkan tenant_id yang aktif
        static::addGlobalScope('tenant', function (Builder $builder) {
            $context = app(TenantContext::class);
            if ($tenantId = $context->id()) {
                $builder->where($builder->getQuery()->from . '.tenant_id', $tenantId);
            }
        });

        // Otomatis isi tenant_id saat menyimpan data baru
        static::creating(function ($model) {
            $context = app(TenantContext::class);
            if ($tenantId = $context->id()) {
                $model->tenant_id = $tenantId;
            }
        });
    }
}