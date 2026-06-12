<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Docente;
use App\Models\Postulante;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Gestion;
use App\Models\Examen;
use App\Models\Nota;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Gestion $activeGestion;
    private Carrera $carrera;
    private Materia $materia;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Administrador');

        // Create initial objects
        $this->activeGestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $this->carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'sigla' => 'SIS',
        ]);

        $this->materia = Materia::create([
            'nombre' => 'Física I',
            'sigla' => 'FIS100',
            'carrera_id' => $this->carrera->id,
        ]);
    }

    /**
     * Test Docente soft deletion cascade to User.
     */
    public function test_docente_deletion_soft_deletes_associated_user(): void
    {
        $userDocente = User::create([
            'name' => 'Docente Test',
            'email' => 'docente@test.com',
            'password' => bcrypt('password'),
        ]);
        $userDocente->assignRole('Docente');

        $docente = Docente::create([
            'user_id' => $userDocente->id,
            'ci' => '1234567',
            'telefono' => '77777777',
            'especialidad' => 'Física',
        ]);

        $this->assertDatabaseHas('docentes', ['id' => $docente->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $userDocente->id, 'deleted_at' => null]);

        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Admin\Docentes::class)
            ->call('delete', $docente->id);

        $this->assertSoftDeleted('docentes', ['id' => $docente->id]);
        $this->assertSoftDeleted('users', ['id' => $userDocente->id]);
    }

    /**
     * Test Postulantes full CRUD lifecycle.
     */
    public function test_postulantes_crud_operations(): void
    {
        $this->actingAs($this->admin);

        // 1. Test Create
        $livewire = Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('openCreate')
            ->set('name', 'Postulante Test')
            ->set('email', 'postulante@test.com')
            ->set('ci', '9876543')
            ->set('telefono', '66666666')
            ->set('fecha_nacimiento', '2000-01-01')
            ->set('sexo', 'M')
            ->set('direccion', 'Calle Falsa 123')
            ->set('colegio_procedencia', 'Colegio Nacional')
            ->set('ciudad', 'Santa Cruz')
            ->set('carrera_primera_opcion_id', $this->carrera->id)
            ->set('gestion_id', $this->activeGestion->id)
            ->set('ci_vigente', true)
            ->set('titulo_bachiller', true)
            ->set('libreta_legalizada', true)
            ->set('pago_realizado', true)
            ->set('pago_matricula_realizado', true)
            ->call('save');

        $livewire->assertHasNoErrors();

        // Verify User was created with role Postulante
        $user = User::where('email', 'postulante@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Postulante'));

        // Verify Postulante was created
        $postulante = Postulante::where('user_id', $user->id)->first();
        $this->assertNotNull($postulante);
        $this->assertEquals('9876543', $postulante->ci);
        $this->assertEquals($this->carrera->id, $postulante->carrera_primera_opcion_id);
        $this->assertTrue((bool)$postulante->ci_vigente);
        $this->assertTrue((bool)$postulante->titulo_bachiller);
        $this->assertTrue((bool)$postulante->libreta_legalizada);
        $this->assertTrue((bool)$postulante->pago_realizado);
        $this->assertTrue((bool)$postulante->pago_matricula_realizado);

        // 2. Test Edit
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('openEdit', $postulante->id)
            ->assertSet('name', 'Postulante Test')
            ->assertSet('email', 'postulante@test.com')
            ->assertSet('ci_vigente', true)
            ->assertSet('titulo_bachiller', true)
            ->assertSet('libreta_legalizada', true)
            ->assertSet('pago_realizado', true)
            ->assertSet('pago_matricula_realizado', true)
            ->set('name', 'Postulante Editado')
            ->set('email', 'postulante_edit@test.com')
            ->set('pago_realizado', false)
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('Postulante Editado', $user->name);
        $this->assertEquals('postulante_edit@test.com', $user->email);

        $postulante->refresh();
        $this->assertFalse((bool)$postulante->pago_realizado);

        // 3. Test Delete
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('delete', $postulante->id);

        $this->assertSoftDeleted('postulantes', ['id' => $postulante->id]);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * Test Examenes validation constraints, reactive weight setting, and applicant score recalculations.
     */
    public function test_examenes_domain_validations_and_recalculations(): void
    {
        $this->actingAs($this->admin);

        // 1. Test reactive weight defaulting based on exam name
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->set('nombre', 'Primer Parcial')
            ->assertSet('ponderacion', 30)
            ->set('nombre', 'Segundo Parcial')
            ->assertSet('ponderacion', 30)
            ->set('nombre', 'Examen Final')
            ->assertSet('ponderacion', 40);

        // 2. Test constraint: Valid name input
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->set('nombre', 'Parcial Invalido')
            ->set('materia_id', $this->materia->id)
            ->set('gestion_id', $this->activeGestion->id)
            ->set('ponderacion', 30)
            ->set('fecha', '2026-03-01')
            ->call('save')
            ->assertHasErrors(['nombre']);

        // 3. Test duplicate check
        Examen::create([
            'nombre' => 'Primer Parcial',
            'materia_id' => $this->materia->id,
            'gestion_id' => $this->activeGestion->id,
            'ponderacion' => 30,
            'fecha' => '2026-03-01',
        ]);

        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->set('nombre', 'Primer Parcial')
            ->set('materia_id', $this->materia->id)
            ->set('gestion_id', $this->activeGestion->id)
            ->set('ponderacion', 30)
            ->set('fecha', '2026-03-01')
            ->call('save')
            ->assertHasErrors(['nombre']);

        // 4. Test weight sum limit (> 100%)
        // Current: Primer Parcial (30%) is already registered.
        // Let's try to add a Segundo Parcial but with 80% (total = 110%)
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->set('nombre', 'Segundo Parcial')
            ->set('materia_id', $this->materia->id)
            ->set('gestion_id', $this->activeGestion->id)
            ->set('ponderacion', 80)
            ->set('fecha', '2026-04-01')
            ->call('save')
            ->assertHasErrors(['ponderacion']);
    }

    /**
     * Test score recalculation trigger on exam creation, update and deletion.
     */
    public function test_score_recalculation_on_exam_lifecycle(): void
    {
        $this->actingAs($this->admin);

        // Create Postulante
        $user = User::create([
            'name' => 'Aspirante A',
            'email' => 'aspirante@test.com',
            'password' => bcrypt('password'),
        ]);
        $postulante = Postulante::create([
            'user_id' => $user->id,
            'ci' => '777666',
            'fecha_nacimiento' => '2002-05-15',
            'carrera_primera_opcion_id' => $this->carrera->id,
            'gestion_id' => $this->activeGestion->id,
            'estado_admision' => 'pendiente',
        ]);

        // Register two exams (total = 60%)
        $exam1 = Examen::create([
            'nombre' => 'Primer Parcial',
            'materia_id' => $this->materia->id,
            'gestion_id' => $this->activeGestion->id,
            'ponderacion' => 30,
            'fecha' => '2026-03-01',
        ]);

        $exam2 = Examen::create([
            'nombre' => 'Segundo Parcial',
            'materia_id' => $this->materia->id,
            'gestion_id' => $this->activeGestion->id,
            'ponderacion' => 30,
            'fecha' => '2026-04-01',
        ]);

        // Add grades for this applicant: 100 on Parcial 1, 100 on Parcial 2
        Nota::create(['postulante_id' => $postulante->id, 'examen_id' => $exam1->id, 'puntaje' => 100.00]);
        Nota::create(['postulante_id' => $postulante->id, 'examen_id' => $exam2->id, 'puntaje' => 100.00]);

        // Trigger manual recalculate (missing Examen Final, so it should be pending with 60 points)
        $examService = new \App\Services\ExamService();
        $examService->recalculatePostulanteScore($postulante->id, $this->activeGestion->id);

        $postulante->refresh();
        $this->assertEquals(60.00, $postulante->nota_final);
        $this->assertEquals('pendiente', $postulante->estado_admision);

        // 1. Create the final exam using the controller
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->set('nombre', 'Examen Final')
            ->set('materia_id', $this->materia->id)
            ->set('gestion_id', $this->activeGestion->id)
            ->set('ponderacion', 40)
            ->set('fecha', '2026-05-01')
            ->call('save')
            ->assertHasNoErrors();

        // Get the created exam final
        $examFinal = Examen::where('nombre', 'Examen Final')->first();
        $this->assertNotNull($examFinal);

        // Add grade for final exam: 50
        Nota::create(['postulante_id' => $postulante->id, 'examen_id' => $examFinal->id, 'puntaje' => 50.00]);

        // Trigger update weight of Examen Final to 40 via controller (recalculates automatically)
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->call('openEdit', $examFinal->id)
            ->set('ponderacion', 40)
            ->call('save')
            ->assertHasNoErrors();

        // Final grade = 30% of 100 + 30% of 100 + 40% of 50 = 30 + 30 + 20 = 80.00
        // Because all exams exist (sum to 100%), it should be admitido_primera_opcion (80 >= 60)
        $postulante->refresh();
        $this->assertEquals(80.00, $postulante->nota_final);
        $this->assertEquals('admitido_primera_opcion', $postulante->estado_admision);

        // 2. Delete the exam final via controller (recalculates automatically back to pending / 60 points)
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->call('delete', $examFinal->id);

        $postulante->refresh();
        $this->assertEquals(60.00, $postulante->nota_final);
        $this->assertEquals('pendiente', $postulante->estado_admision);
    }

    /**
     * Test voice commands filter parsing.
     */
    public function test_postulantes_voice_filtering(): void
    {
        $this->actingAs($this->admin);

        // Create another career to test with sigla INF
        $carreraInf = Carrera::create([
            'nombre' => 'Ingeniería Informática',
            'sigla' => 'INF',
        ]);

        // Create another career to test with sigla RED
        $carreraRed = Carrera::create([
            'nombre' => 'Ingeniería de Redes y Telecomunicaciones',
            'sigla' => 'RED',
        ]);

        // 1. Test Career systems command
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'mostrar postulantes de sistemas')
            ->assertSet('filterCarrera', $this->carrera->id);

        // 2. Test Career informatica command
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'filtrar por informatica')
            ->assertSet('filterCarrera', $carreraInf->id);

        // 3. Test Estado command
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'mostrar reprobados')
            ->assertSet('filterEstado', 'reprobado');

        // 4. Test Note filter command
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'nota mayor a 75')
            ->assertSet('filterNotaMin', '75');

        // Test optional "final" keyword and number word translation in note filter
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'nota final 50')
            ->assertSet('filterNotaMin', '50');

        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'nota final cincuenta')
            ->assertSet('filterNotaMin', '50');

        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'nota final mayor a cincuenta')
            ->assertSet('filterNotaMin', '50');

        // 5. Complex Case: second option admission for networks
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'admitidos en segunda opción de redes')
            ->assertSet('filterCarrera', $carreraRed->id)
            ->assertSet('filterEstado', 'admitido_segunda_opcion');

        // 6. Complex Case: search by full name
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'buscar juan perez')
            ->assertSet('search', 'juan perez');

        // 7. Complex Case: absent (no presentado) for systems
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'no presentados de sistemas')
            ->assertSet('filterCarrera', $this->carrera->id)
            ->assertSet('filterEstado', 'no_presentado');

        // 8. Test Clear command
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('processVoiceCommand', 'limpiar filtros')
            ->assertSet('filterCarrera', '')
            ->assertSet('filterEstado', '')
            ->assertSet('filterNotaMin', '')
            ->assertSet('search', '');
    }

    /**
     * Test voice command filter parsing in Carreras component.
     */
    public function test_carreras_voice_filtering(): void
    {
        $this->actingAs($this->admin);

        // 1. Search name/sigla
        Livewire::test(\App\Livewire\Admin\Carreras::class)
            ->call('processVoiceCommand', 'buscar sistemas')
            ->assertSet('search', 'sistemas');

        // 2. Direct query without prefix
        Livewire::test(\App\Livewire\Admin\Carreras::class)
            ->call('processVoiceCommand', 'SIS')
            ->assertSet('search', 'sis');

        // 3. Clear filters
        Livewire::test(\App\Livewire\Admin\Carreras::class)
            ->call('processVoiceCommand', 'limpiar filtros')
            ->assertSet('search', '');
    }

    /**
     * Test voice command filter parsing in Materias component.
     */
    public function test_materias_voice_filtering(): void
    {
        $this->actingAs($this->admin);

        $carreraInf = Carrera::create([
            'nombre' => 'Ingeniería Informática',
            'sigla' => 'INF',
        ]);

        // 1. Filter by Systems career
        Livewire::test(\App\Livewire\Admin\Materias::class)
            ->call('processVoiceCommand', 'filtrar por sistemas')
            ->assertSet('filterCarrera', $this->carrera->id);

        // 2. Filter by Informatics career
        Livewire::test(\App\Livewire\Admin\Materias::class)
            ->call('processVoiceCommand', 'mostrar informatica')
            ->assertSet('filterCarrera', $carreraInf->id);

        // 3. Search text
        Livewire::test(\App\Livewire\Admin\Materias::class)
            ->call('processVoiceCommand', 'buscar fisica')
            ->assertSet('search', 'fisica');

        // 4. Reset
        Livewire::test(\App\Livewire\Admin\Materias::class)
            ->call('processVoiceCommand', 'limpiar')
            ->assertSet('filterCarrera', '')
            ->assertSet('search', '');
    }

    /**
     * Test voice command filter parsing in Docentes component.
     */
    public function test_docentes_voice_filtering(): void
    {
        $this->actingAs($this->admin);

        // 1. Search name
        Livewire::test(\App\Livewire\Admin\Docentes::class)
            ->call('processVoiceCommand', 'buscar pedro perez')
            ->assertSet('search', 'pedro perez');

        // 2. Search specialty
        Livewire::test(\App\Livewire\Admin\Docentes::class)
            ->call('processVoiceCommand', 'especialidad de matematicas')
            ->assertSet('search', 'matematicas');

        // 3. Search specialty with complex phrase
        Livewire::test(\App\Livewire\Admin\Docentes::class)
            ->call('processVoiceCommand', 'mostrar docentes con especialidad en fisica')
            ->assertSet('search', 'fisica');

        // 4. Search specialty with filter prefix
        Livewire::test(\App\Livewire\Admin\Docentes::class)
            ->call('processVoiceCommand', 'filtrar por especialidad computacion')
            ->assertSet('search', 'computacion');

        // 5. Clear
        Livewire::test(\App\Livewire\Admin\Docentes::class)
            ->call('processVoiceCommand', 'quitar filtros')
            ->assertSet('search', '');
    }

    /**
     * Test voice command filter parsing in Examenes component.
     */
    public function test_examenes_voice_filtering(): void
    {
        $this->actingAs($this->admin);

        // 1. Filter by physics (matches default 'Física I' in database)
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->call('processVoiceCommand', 'ver examenes de fisica')
            ->assertSet('filterMateria', $this->materia->id);

        // 2. Filter by gestion
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->call('processVoiceCommand', 'gestion I-2026')
            ->assertSet('filterGestion', $this->activeGestion->id);

        // 3. Search specific exam
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->call('processVoiceCommand', 'primer parcial')
            ->assertSet('search', 'Primer Parcial');

        // 4. Reset
        Livewire::test(\App\Livewire\Admin\Examenes::class)
            ->call('processVoiceCommand', 'restablecer')
            ->assertSet('filterMateria', '')
            ->assertSet('filterGestion', '')
            ->assertSet('search', '');
    }

    /**
     * Test Admin document checks, enablement, Stripe simulation, and self-enrollment blocks.
     */
    public function test_postulante_enablement_stripe_and_self_enrollment(): void
    {
        $this->actingAs($this->admin);

        // 1. Create a Postulante with incomplete documents
        $livewire = Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('openCreate')
            ->set('name', 'Postulante Incompleto')
            ->set('email', 'incompleto@test.com')
            ->set('ci', '1231231')
            ->set('telefono', '77777777')
            ->set('fecha_nacimiento', '2000-01-01')
            ->set('sexo', 'M')
            ->set('direccion', 'Av Principal 456')
            ->set('colegio_procedencia', 'Colegio Nacional')
            ->set('ciudad', 'Santa Cruz')
            ->set('carrera_primera_opcion_id', $this->carrera->id)
            ->set('gestion_id', $this->activeGestion->id)
            ->set('ci_vigente', true)
            ->set('titulo_bachiller', false) // Missing
            ->set('libreta_legalizada', true)
            ->call('save');

        $livewire->assertHasNoErrors();

        $postulante = Postulante::where('ci', '1231231')->first();
        $this->assertNotNull($postulante);
        $this->assertFalse((bool)$postulante->habilitado);
        $this->assertStringContainsString('Título de Bachiller', $postulante->mensaje_documentos);

        // 2. Logging in as this student: payment is required
        $this->actingAs($postulante->user);
        $portal = Livewire::test(\App\Livewire\DashboardPortal::class);
        $portal->assertSet('role', 'Postulante');

        // Verify they cannot self-enroll yet
        $portal->call('enroll');
        $portal->assertSet('errorMessage', 'Debes pagar tu inscripción para poder inscribirte a las materias.');

        // 3. Simulate payment via Stripe checkout route
        $response = $this->get('/stripe/checkout');
        $response->assertRedirect();
        
        $postulante->refresh();
        $this->assertTrue((bool)$postulante->pago_realizado);

        // 4. Try self-enrolling again, should fail because habilitado is false
        $portal = Livewire::test(\App\Livewire\DashboardPortal::class);
        $portal->call('enroll');
        $portal->assertSet('errorMessage', 'Tu perfil debe ser habilitado por el administrador.');

        // 5. Admin enables the applicant by completing requirements
        $this->actingAs($this->admin);
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('openEdit', $postulante->id)
            ->set('titulo_bachiller', true) // Completed!
            ->call('save');

        $postulante->refresh();
        $this->assertTrue((bool)$postulante->habilitado);
        $this->assertNull($postulante->mensaje_documentos);

        // 6. Student self-enrolls
        $this->actingAs($postulante->user);
        
        // Create a group for our subject
        $grupo = \App\Models\Grupo::create([
            'nombre' => 'SIS-110 - G1',
            'materia_id' => $this->materia->id,
            'gestion_id' => $this->activeGestion->id,
            'cupo_maximo' => 60,
        ]);

        $portal = Livewire::test(\App\Livewire\DashboardPortal::class)
            ->set('selectedGroups', [$this->materia->id => $grupo->id])
            ->call('enroll');

        $portal->assertHasNoErrors();
        $this->assertTrue($postulante->grupos()->where('grupo_id', $grupo->id)->exists());
    }

    /**
     * Test changing admission state validation in Postulantes component.
     */
    public function test_postulantes_cambiar_estado_validation(): void
    {
        $this->actingAs($this->admin);

        $user = User::create([
            'name' => 'State Test User',
            'email' => 'statetest@example.com',
            'password' => bcrypt('password'),
        ]);

        $postulante = Postulante::create([
            'user_id' => $user->id,
            'nombres_apellidos' => 'Test State Change candidate',
            'ci' => '9999999',
            'telefono' => '77777777',
            'fecha_nacimiento' => '2000-01-01',
            'sexo' => 'M',
            'direccion' => 'Test address',
            'colegio_procedencia' => 'Test school',
            'ciudad' => 'Test city',
            'carrera_primera_opcion_id' => $this->carrera->id,
            'gestion_id' => $this->activeGestion->id,
            'estado_admision' => 'pendiente',
        ]);

        // Create exams and grades so recalculate score results in 'admitido_primera_opcion'
        $exam1 = Examen::create([
            'nombre' => 'Primer Parcial',
            'materia_id' => $this->materia->id,
            'gestion_id' => $this->activeGestion->id,
            'ponderacion' => 30,
            'fecha' => '2026-03-01',
        ]);

        $exam2 = Examen::create([
            'nombre' => 'Segundo Parcial',
            'materia_id' => $this->materia->id,
            'gestion_id' => $this->activeGestion->id,
            'ponderacion' => 30,
            'fecha' => '2026-04-01',
        ]);

        $exam3 = Examen::create([
            'nombre' => 'Examen Final',
            'materia_id' => $this->materia->id,
            'gestion_id' => $this->activeGestion->id,
            'ponderacion' => 40,
            'fecha' => '2026-05-01',
        ]);

        Nota::create(['postulante_id' => $postulante->id, 'examen_id' => $exam1->id, 'puntaje' => 100.00]);
        Nota::create(['postulante_id' => $postulante->id, 'examen_id' => $exam2->id, 'puntaje' => 100.00]);
        Nota::create(['postulante_id' => $postulante->id, 'examen_id' => $exam3->id, 'puntaje' => 100.00]);

        // 1. Changing to a valid state: 'admitido_primera_opcion'
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('cambiarEstado', $postulante->id, 'admitido_primera_opcion')
            ->assertHasNoErrors();

        $postulante->refresh();
        $this->assertEquals('admitido_primera_opcion', $postulante->estado_admision);

        // 2. Changing to an invalid state: 'aprobados' (virtual state)
        // This should return early and not update the database state, preventing the check violation
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('cambiarEstado', $postulante->id, 'aprobados')
            ->assertHasNoErrors();

        // Database state should remain 'admitido_primera_opcion'
        $postulante->refresh();
        $this->assertEquals('admitido_primera_opcion', $postulante->estado_admision);
    }

    /**
     * Test the newly added admission export and print routes.
     */
    public function test_admin_export_routes(): void
    {
        $this->actingAs($this->admin);

        // Test export admitted route
        $responseAdmitidos = $this->get(route('admin.exportar.admitidos', ['gestion_id' => $this->activeGestion->id]));
        $responseAdmitidos->assertStatus(200);
        $responseAdmitidos->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Test export non-admitted route
        $responseNoAdmitidos = $this->get(route('admin.exportar.no-admitidos', ['gestion_id' => $this->activeGestion->id]));
        $responseNoAdmitidos->assertStatus(200);
        $responseNoAdmitidos->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Test print admission report route
        $responsePrint = $this->get(route('admin.reporte-admision.imprimir', ['gestion_id' => $this->activeGestion->id]));
        $responsePrint->assertStatus(200);
        $responsePrint->assertViewIs('reports.print-admisiones');
        $responsePrint->assertViewHas('admitidosPorCarrera');
    }
}
