<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_only_sees_their_own_tenant_in_shared_props(): void
    {
        $tenantA = Tenant::create(['name' => 'Tienda A', 'slug' => 'tienda-a']);
        Tenant::create(['name' => 'Tienda B', 'slug' => 'tienda-b']);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);

        $this->actingAs($userA)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('tenant.name', 'Tienda A'));
    }

    public function test_current_tenant_is_resolved_from_the_authenticated_user(): void
    {
        $tenant = Tenant::create(['name' => 'Tienda C', 'slug' => 'tienda-c']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $this->assertSame($tenant->id, app(CurrentTenant::class)->id());
    }

    public function test_a_guest_request_has_no_tenant_context(): void
    {
        $this->assertFalse(app(CurrentTenant::class)->check());
    }
}
