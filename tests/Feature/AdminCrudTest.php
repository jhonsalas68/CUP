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

        // 2. Test Edit
        Livewire::test(\App\Livewire\Admin\Postulantes::class)
            ->call('openEdit', $postulante->id)
            ->assertSet('name', 'Postulante Test')
            ->assertSet('email', 'postulante@test.com')
            ->assertSet('ci_vigente', true)
            ->assertSet('titulo_bachiller', true)
            ->assertSet('libreta_legalizada', true)
            ->set('name', 'Postulante Editado')
            ->set('email', 'postulante_edit@test.com')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('Postulante Editado', $user->name);
        $this->assertEquals('postulante_edit@test.com', $user->email);

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
}
