<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Models\Bitacora;
use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\User;
use App\Services\AdmissionSelectionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BitacoraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_bitacora_requires_authentication()
    {
        $this->get(route('admin.bitacora'))
            ->assertRedirect(route('login'));
    }

    public function test_bitacora_requires_administrador_role()
    {
        // Test Coordinador
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');

        $this->actingAs($coordinador)
            ->get(route('admin.bitacora'))
            ->assertStatus(403);

        // Test Docente
        $docente = User::factory()->create();
        $docente->assignRole('Docente');

        $this->actingAs($docente)
            ->get(route('admin.bitacora'))
            ->assertStatus(403);

        // Test Postulante
        $postulante = User::factory()->create();
        $postulante->assignRole('Postulante');

        $this->actingAs($postulante)
            ->get(route('admin.bitacora'))
            ->assertStatus(403);
    }

    public function test_bitacora_accessible_by_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $this->actingAs($admin)
            ->get(route('admin.bitacora'))
            ->assertStatus(200)
            ->assertSee('Bitácora de Actividades');
    }

    public function test_bitacora_registers_crud_actions()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $this->actingAs($admin);

        // 1. Create a model (Carrera)
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas Especiales',
            'sigla' => 'SIS-ESP',
        ]);

        $this->assertDatabaseHas('bitacora', [
            'action' => 'crear',
            'objeto' => "Carrera #{$carrera->id}",
            'user_id' => $admin->id,
        ]);

        // Verify payload contains created attributes
        $logCrear = Bitacora::where('action', 'crear')->first();
        $this->assertNotNull($logCrear);
        $this->assertEquals('Ingeniería de Sistemas Especiales', $logCrear->payload['nombre']);
        $this->assertEquals('SIS-ESP', $logCrear->payload['sigla']);

        // 2. Update model
        $carrera->update([
            'nombre' => 'Ingeniería de Sistemas Espaciales',
        ]);

        $this->assertDatabaseHas('bitacora', [
            'action' => 'actualizar',
            'objeto' => "Carrera #{$carrera->id}",
            'user_id' => $admin->id,
        ]);

        // Verify payload contains diff (dirty and original)
        $logActualizar = Bitacora::where('action', 'actualizar')->first();
        $this->assertNotNull($logActualizar);
        $this->assertEquals('Ingeniería de Sistemas Espaciales', $logActualizar->payload['dirty']['nombre']);
        $this->assertEquals('Ingeniería de Sistemas Especiales', $logActualizar->payload['original']['nombre']);

        // 3. Delete model
        $carreraId = $carrera->id;
        $carrera->delete();

        $this->assertDatabaseHas('bitacora', [
            'action' => 'eliminar',
            'objeto' => "Carrera #{$carreraId}",
            'user_id' => $admin->id,
        ]);
    }

    public function test_bitacora_records_run_admission_process()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $this->actingAs($admin);

        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $this->mock(AdmissionSelectionService::class, function ($mock) use ($gestion) {
            $mock->shouldReceive('processAdmissions')
                ->with($gestion->id)
                ->once()
                ->andReturn(['success' => true]);

            $mock->shouldReceive('getStats')
                ->with($gestion->id)
                ->once()
                ->andReturn([
                    'general' => [
                        'total_postulantes' => 0,
                        'total_admitidos' => 0,
                        'tasa_admision' => 0,
                        'reprobados' => 0,
                        'no_admitidos' => 0,
                    ],
                    'carreras' => [],
                ]);
        });

        Livewire::test(Dashboard::class)
            ->call('runAdmissionProcess');

        // Check Bitacora has the process_admision log
        $this->assertDatabaseHas('bitacora', [
            'action' => 'proceso_admision',
            'objeto' => 'Proceso Admisión',
            'user_id' => $admin->id,
        ]);
    }

    public function test_bitacora_component_search_and_filter()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $this->actingAs($admin);

        // Seed some activities manually to test search and filters
        Bitacora::create([
            'user_id' => $admin->id,
            'action' => 'crear',
            'objeto' => 'Carrera #1',
            'descripcion' => 'Creación de Carrera de prueba',
            'ip_address' => '127.0.0.1',
        ]);

        Bitacora::create([
            'user_id' => $admin->id,
            'action' => 'eliminar',
            'objeto' => 'Docente #3',
            'descripcion' => 'Eliminación del Docente titular',
            'ip_address' => '192.168.1.5',
        ]);

        // Test filter by action
        Livewire::test(\App\Livewire\Admin\Bitacora::class)
            ->set('selectedAction', 'crear')
            ->assertSee('Creación de Carrera de prueba')
            ->assertDontSee('Eliminación del Docente titular')
            ->set('selectedAction', 'eliminar')
            ->assertSee('Eliminación del Docente titular')
            ->assertDontSee('Creación de Carrera de prueba');

        // Test search
        Livewire::test(\App\Livewire\Admin\Bitacora::class)
            ->set('search', '192.168.1.5')
            ->assertSee('Eliminación del Docente titular')
            ->assertDontSee('Creación de Carrera de prueba')
            ->set('search', 'Carrera')
            ->assertSee('Creación de Carrera de prueba')
            ->assertDontSee('Eliminación del Docente titular');
    }

    public function test_bitacora_component_clear_logs()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $this->actingAs($admin);

        Bitacora::create([
            'user_id' => $admin->id,
            'action' => 'crear',
            'objeto' => 'Carrera #1',
            'descripcion' => 'Creación de Carrera de prueba',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseCount('bitacora', 1);

        Livewire::test(\App\Livewire\Admin\Bitacora::class)
            ->call('clearLogs')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('bitacora', 0);
    }
}
