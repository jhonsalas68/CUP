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

    public function test_admin_dashboard_can_open_admission_modal()
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
            ->assertSet('showAdmissionModal', false)
            ->call('openAdmissionProcess')
            ->assertSet('showAdmissionModal', true);
    }

    public function test_admin_dashboard_can_run_admission_process()
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

        $this->mock(\App\Services\AdmissionSelectionService::class, function ($mock) use ($gestion) {
            $mock->shouldReceive('processAdmissions')
                ->with($gestion->id)
                ->once()
                ->andReturn(['success' => true]);

            $mock->shouldReceive('getStats')
                ->with($gestion->id)
                ->once()
                ->andReturn([
                    'general' => [
                        'total_postulantes' => 10,
                        'total_admitidos' => 6,
                        'tasa_admision' => 60,
                        'reprobados' => 2,
                        'no_admitidos' => 2,
                    ],
                    'carreras' => [
                        'SIS' => [
                            'nombre' => 'Sistemas',
                            'inscritos_primera_opcion' => 10,
                            'cupo_primera_opcion' => 5,
                            'cupo_segunda_opcion' => 2,
                            'admitidos_primera_opcion' => 5,
                            'admitidos_segunda_opcion' => 1,
                            'no_admitidos' => 2,
                            'nota_minima_ingreso' => 61.50,
                        ]
                    ]
                ]);
        });

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('runAdmissionProcess')
            ->assertSet('isProcessing', false)
            ->assertSet('admissionError', null)
            ->assertNotSet('admissionStats', null);
    }

    public function test_admin_dashboard_can_show_group_details()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $carrera = \App\Models\Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'sigla' => 'SIS',
        ]);

        $materia = \App\Models\Materia::create([
            'nombre' => 'Programación I',
            'sigla' => 'SIS-110',
            'carrera_id' => $carrera->id,
        ]);

        $grupo = \App\Models\Grupo::create([
            'nombre' => 'SIS-110 - G1',
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'cupo_maximo' => 60,
        ]);

        $userDocente = User::create([
            'name' => 'Docente Uno',
            'email' => 'docente1@example.com',
            'password' => bcrypt('password'),
        ]);
        $docente = \App\Models\Docente::create([
            'user_id' => $userDocente->id,
            'ci' => '8888881',
            'especialidad' => 'Programación',
            'disponibilidad_horaria' => ['slot_1'],
            'formacion_academica' => 'Lic',
        ]);
        $grupo->docentes()->attach($docente->id);

        $userPost = User::create([
            'name' => 'Postulante A',
            'email' => 'postulanteA@example.com',
            'password' => bcrypt('password'),
        ]);
        $postulante = \App\Models\Postulante::create([
            'user_id' => $userPost->id,
            'ci' => '123456',
            'telefono' => '700000',
            'fecha_nacimiento' => '2005-01-01',
            'carrera_primera_opcion_id' => $carrera->id,
            'gestion_id' => $gestion->id,
            'estado_admision' => 'pendiente',
        ]);
        $grupo->postulantes()->attach($postulante->id);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('showGroupDetails', $grupo->id)
            ->assertSet('showGroupDetailsModal', true)
            ->assertSet('selectedGroupInfo.nombre', 'SIS-110 - G1')
            ->assertSet('selectedGroupInfo.docente', 'Docente Uno')
            ->assertCount('groupAlumnos', 1)
            ->assertSet('groupAlumnos.0.nombre', 'Postulante A')
            ->call('closeGroupDetails')
            ->assertSet('showGroupDetailsModal', false)
            ->assertSet('selectedGroupInfo', null);
    }
}

