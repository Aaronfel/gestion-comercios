<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Tenancy\CurrentTenant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\TenantThing;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('tenant_things')) {
            Schema::create('tenant_things', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id');
                $table->string('name');
            });
        }
    }

    public function test_a_tenant_cannot_see_another_tenants_rows(): void
    {
        $a = Tenant::create(['name' => 'A', 'slug' => 'a']);
        $b = Tenant::create(['name' => 'B', 'slug' => 'b']);

        $current = app(CurrentTenant::class);

        // Como tenant A: creo una fila (tenant_id se autocompleta).
        $current->set($a);
        TenantThing::create(['name' => 'de A']);

        // Como tenant B: NO ve la fila de A.
        $current->set($b);
        $this->assertSame(0, TenantThing::count());

        // Como tenant A: la ve, con el tenant_id correcto.
        $current->set($a);
        $this->assertSame(1, TenantThing::count());
        $this->assertSame($a->id, TenantThing::first()->tenant_id);
    }

    public function test_tenant_id_is_auto_filled_on_create(): void
    {
        $a = Tenant::create(['name' => 'A', 'slug' => 'a']);
        app(CurrentTenant::class)->set($a);

        $thing = TenantThing::create(['name' => 'x']);

        $this->assertSame($a->id, $thing->tenant_id);
    }
}
