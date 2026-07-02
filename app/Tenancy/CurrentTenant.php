<?php

namespace App\Tenancy;

use App\Models\Tenant;

/**
 * Holds the tenant for the current request. Bound as a singleton in
 * AppServiceProvider and set by the SetCurrentTenant middleware. The
 * TenantScope reads it to filter every BelongsToTenant model.
 */
class CurrentTenant
{
    protected ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }
}
