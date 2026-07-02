<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to every tenant-owned domain model (Product, Sale, Customer, ...).
 * It adds the tenant global scope and auto-fills tenant_id on create from the
 * current tenant. Requires a `tenant_id` column on the model's table.
 *
 * Usage:
 *   class Product extends Model { use BelongsToTenant; }
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $current = app(CurrentTenant::class);

            if (empty($model->tenant_id) && $current->check()) {
                $model->tenant_id = $current->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
