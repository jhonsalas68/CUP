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

    public function test_examenes_component_can_render_and_update_grades()
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
            'nombre' => 'SIS',
            'sigla' => 'SIS',
        ]);

        $materia = \App\Models\Materia::create([
            'nombre' => 'Programación',
            'sigla' => 'SIS-110',
            'carrera_id' => $carrera->id,
        ]);

        $grupo = \App\Models\Grupo::create([
            'nombre' => 'SIS-110 - G1',
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'cupo_maximo' => 60,
        ]);

        $userPost = User::create([
            'name' => 'Postulante B',
            'email' => 'postulanteB@example.com',
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

        // Enroll postulant in the group
        $grupo->postulantes()->attach($postulante->id);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->assertSet('activeTab', 'calificaciones')
            ->assertSee('Calificaciones por Postulante')
            ->assertSee('Postulante B')
            ->call('openEditNotas', $postulante->id, $materia->id, $grupo->nombre, $materia->nombre)
            ->assertSet('showEditNotasModal', true)
            ->set('nota1erParcial', 80)
            ->set('nota2doParcial', 90)
            ->set('nota3erParcial', 85)
            ->call('saveNotas')
            ->assertSet('showEditNotasModal', false);

        // Verify database grades
        $this->assertDatabaseHas('notas', [
            'postulante_id' => $postulante->id,
            'puntaje' => 80,
        ]);

        $this->assertDatabaseHas('notas', [
            'postulante_id' => $postulante->id,
            'puntaje' => 90,
        ]);

        $this->assertDatabaseHas('notas', [
            'postulante_id' => $postulante->id,
            'puntaje' => 85,
        ]);

        // Verify recalculated final score: (80 * 0.3) + (90 * 0.3) + (85 * 0.4) = 24 + 27 + 34 = 85
        $postulante->refresh();
        $this->assertEquals(85.00, $postulante->nota_final);
        $this->assertEquals('admitido_primera_opcion', $postulante->estado_admision);
    }

    public function test_examenes_component_can_filter_by_grade_voice_command()
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
            'nombre' => 'SIS',
            'sigla' => 'SIS',
        ]);

        $materia = \App\Models\Materia::create([
            'nombre' => 'Programación',
            'sigla' => 'SIS-110',
            'carrera_id' => $carrera->id,
        ]);

        $grupo = \App\Models\Grupo::create([
            'nombre' => 'SIS-110 - G1',
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'cupo_maximo' => 60,
        ]);

        // Student 1 (Admitted - score 80)
        $user1 = User::create(['name' => 'Student High', 'email' => 'high@example.com', 'password' => 'password']);
        $post1 = \App\Models\Postulante::create([
            'user_id' => $user1->id, 'ci' => '1', 'telefono' => '1', 'fecha_nacimiento' => '2005-01-01',
            'carrera_primera_opcion_id' => $carrera->id, 'gestion_id' => $gestion->id, 'estado_admision' => 'pendiente'
        ]);
        $grupo->postulantes()->attach($post1->id);

        // Student 2 (Failed - score 40)
        $user2 = User::create(['name' => 'Student Low', 'email' => 'low@example.com', 'password' => 'password']);
        $post2 = \App\Models\Postulante::create([
            'user_id' => $user2->id, 'ci' => '2', 'telefono' => '2', 'fecha_nacimiento' => '2005-01-01',
            'carrera_primera_opcion_id' => $carrera->id, 'gestion_id' => $gestion->id, 'estado_admision' => 'pendiente'
        ]);
        $grupo->postulantes()->attach($post2->id);

        // Create exam definitions
        $exam = \App\Models\Examen::create([
            'nombre' => 'Examen Final',
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'ponderacion' => 100.00,
            'fecha' => now()->format('Y-m-d'),
        ]);

        // Grades
        \App\Models\Nota::create(['postulante_id' => $post1->id, 'examen_id' => $exam->id, 'puntaje' => 80]);
        \App\Models\Nota::create(['postulante_id' => $post2->id, 'examen_id' => $exam->id, 'puntaje' => 40]);

        $this->actingAs($admin);

        // Test filtering by voice command "nota ponderada mayor de setenta"
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->assertSet('activeTab', 'calificaciones')
            ->assertSee('Student High')
            ->assertSee('Student Low')
            ->call('processVoiceCommand', 'nota ponderada mayor de setenta')
            ->assertSet('filterNotaMin', 70)
            ->assertSee('Student High')
            ->assertDontSee('Student Low');

        // Test filtering by voice command "nota menor de sesenta"
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->assertSet('activeTab', 'calificaciones')
            ->assertSee('Student High')
            ->assertSee('Student Low')
            ->call('processVoiceCommand', 'nota menor de sesenta')
            ->assertSet('filterNotaMax', 60)
            ->assertSee('Student Low')
            ->assertDontSee('Student High');
    }

    public function test_admin_dashboard_shows_admitted_students_detail()
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

        $carreraSIS = \App\Models\Carrera::create([
            'sigla' => 'SIS',
            'nombre' => 'Ingeniería de Sistemas',
        ]);

        $carreraINF = \App\Models\Carrera::create([
            'sigla' => 'INF',
            'nombre' => 'Ingeniería Informática',
        ]);

        $user1 = User::factory()->create(['name' => 'Admitido Uno']);
        $postulante1 = \App\Models\Postulante::create([
            'user_id' => $user1->id,
            'nombres_apellidos' => 'Admitido Uno',
            'ci' => '123456',
            'sexo' => 'M',
            'carrera_primera_opcion_id' => $carreraSIS->id,
            'carrera_segunda_opcion_id' => null,
            'gestion_id' => $gestion->id,
            'estado_admision' => 'admitido_primera_opcion',
            'nota_final' => 85.50,
            'ci_vigente' => true,
            'titulo_bachiller' => true,
            'libreta_legalizada' => true,
            'habilitado' => true,
            'pago_realizado' => true,
        ]);

        $user2 = User::factory()->create(['name' => 'Admitido Dos']);
        $postulante2 = \App\Models\Postulante::create([
            'user_id' => $user2->id,
            'nombres_apellidos' => 'Admitido Dos',
            'ci' => '789012',
            'sexo' => 'F',
            'carrera_primera_opcion_id' => $carreraSIS->id,
            'carrera_segunda_opcion_id' => $carreraINF->id,
            'gestion_id' => $gestion->id,
            'estado_admision' => 'admitido_segunda_opcion',
            'nota_final' => 75.00,
            'ci_vigente' => true,
            'titulo_bachiller' => true,
            'libreta_legalizada' => true,
            'habilitado' => true,
            'pago_realizado' => true,
        ]);

        // Test checking systems career (SIS) shows only Admitido Uno (1ra opción)
        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('selectedGestionId', $gestion->id)
            ->set('selectedDetailCarreraId', $carreraSIS->id)
            ->call('loadStats')
            ->assertSet('selectedDetailCarreraId', $carreraSIS->id)
            ->assertSee('Admitido Uno')
            ->assertDontSee('Admitido Dos')
            ->assertSee('85.50')
            ->assertSee('1ra Opción');

        // Test checking informatics career (INF) shows only Admitido Dos (2da opción)
        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('selectedGestionId', $gestion->id)
            ->set('selectedDetailCarreraId', $carreraINF->id)
            ->call('loadStats')
            ->assertSet('selectedDetailCarreraId', $carreraINF->id)
            ->assertSee('Admitido Dos')
            ->assertDontSee('Admitido Uno')
            ->assertSee('75.00')
            ->assertSee('2da Opción');
    }

    public function test_admin_dashboard_can_send_email_notifications()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $carrera = \App\Models\Carrera::create([
            'nombre' => 'SIS',
            'sigla' => 'SIS',
        ]);

        // Create evaluated student (admitido_primera_opcion)
        $userPost = User::create([
            'name' => 'Postulante Notif',
            'email' => 'notif@example.com',
            'password' => bcrypt('password'),
        ]);
        $postulante = \App\Models\Postulante::create([
            'user_id' => $userPost->id,
            'nombres_apellidos' => 'Postulante Notif',
            'ci' => '1234567',
            'telefono' => '700000',
            'fecha_nacimiento' => '2005-01-01',
            'carrera_primera_opcion_id' => $carrera->id,
            'gestion_id' => $gestion->id,
            'estado_admision' => 'admitido_primera_opcion',
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('selectedGestionId', $gestion->id)
            ->call('sendEmailNotifications')
            ->assertHasNoErrors()
            ->assertStatus(200);

        // Assert mail was queued
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\AdmissionResultMail::class, function ($mail) use ($postulante) {
            return $mail->postulante->id === $postulante->id && $mail->hasTo('notif@example.com');
        });

        // Assert Bitacora log was created
        $this->assertDatabaseHas('bitacora', [
            'action' => 'proceso_admision',
            'objeto' => 'Notificaciones Gmail',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_dashboard_can_send_test_email()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('sendTestEmail')
            ->assertHasNoErrors()
            ->assertStatus(200);

        // Assert test email was sent
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\AdmissionResultMail::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });

        // Assert Bitacora log was created
        $this->assertDatabaseHas('bitacora', [
            'action' => 'proceso_admision',
            'objeto' => 'Correo de Prueba',
            'user_id' => $admin->id,
        ]);
    }
}



