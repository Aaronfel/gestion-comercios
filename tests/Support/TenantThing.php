<?php

namespace Tests\Support;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Throwaway tenant-scoped model used only to test the TenantScope filtering
 * (no real domain model uses BelongsToTenant until Phase 1). Backed by a temp
 * `tenant_things` table created in the test setUp.
 */
class TenantThing extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    public $timestamps = false;
}
