<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait untuk model tenant-owned.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @method static void addGlobalScope(string $scope, \Closure|\Illuminate\Database\Eloquent\Scope $implementation)
 * @method static void creating(\Closure|string $callback)
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);
            if ($context->has()) {
                $builder->where($builder->qualifyColumn('tenant_id'), $context->id());
            }
        });

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);
            if ($context->has() && empty($model->getAttribute('tenant_id'))) {
                $model->setAttribute('tenant_id', $context->id());
            }
        });
    }
}