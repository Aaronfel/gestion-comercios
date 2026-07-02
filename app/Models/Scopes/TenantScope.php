<?php

namespace App\Models\Scopes;

use App\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that constrains every query on a BelongsToTenant model to the
 * current tenant. When no tenant is set (e.g. console, guest), no constraint is
 * applied — callers in those contexts must scope explicitly.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $current = app(CurrentTenant::class);

        if ($current->check()) {
            $builder->where($model->getTable().'.tenant_id', $current->id());
        }
    }
}
