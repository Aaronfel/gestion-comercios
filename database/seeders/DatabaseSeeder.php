<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the demo tenant so the app is usable right after `migrate --seed`.
     * Login: feldmanaaron406@gmail.com / password
     */
    public function run(): void
    {
        foreach (['admin', 'vendedor'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'bondi-indumentaria'],
            [
                'name' => 'Bondi Indumentaria',
                'settings' => ['accent_color' => '#6C4DF2'],
            ],
        );

        $admin = User::firstOrCreate(
            ['email' => 'feldmanaaron406@gmail.com'],
            [
                'name' => 'Camila Ortiz',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
            ],
        );

        $admin->assignRole('admin');
    }
}
