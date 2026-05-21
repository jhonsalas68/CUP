<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Gestion;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_dashboard_requires_authorization()
    {
        // Create user with no roles
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_admin_dashboard_is_accessible_by_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        // Create a gestion
        Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);
    }

    public function test_admin_dashboard_is_accessible_by_coordinador()
    {
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');

        // Create a gestion
        Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $this->actingAs($coordinador)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);
    }

    public function test_admin_dashboard_renders_livewire_component()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->assertSet('selectedGestionId', $gestion->id)
            ->assertSee('Dashboard Administrativo')
            ->assertSee('Total Postulantes');
    }
}
