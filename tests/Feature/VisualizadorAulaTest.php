<?php

namespace Tests\Feature;

use App\Livewire\Admin\VisualizadorAula;
use App\Models\Aula;
use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Grupo;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Postulante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VisualizadorAulaTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    private $postulanteUser;

    private $aula;

    private $grupo;

    private $postulante;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'Postulante']);

        // Create Admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Administrador');

        // Create Postulante user
        $this->postulanteUser = User::factory()->create();
        $this->postulanteUser->assignRole('Postulante');

        // Create academic structure
        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'sigla' => 'SIS',
            'codigo' => '187-3',
            'cupos_primera_opcion' => 50,
            'cupos_segunda_opcion' => 20,
        ]);

        $materia = Materia::create([
            'carrera_id' => $carrera->id,
            'nombre' => 'Matemática I',
            'sigla' => 'MAT101',
        ]);

        // Create Aula
        $this->aula = Aula::create([
            'nombre' => 'Aula 101',
            'capacidad' => 10,
            'ubicacion' => 'Pabellón A',
        ]);

        // Create Grupo
        $this->grupo = Grupo::create([
            'nombre' => 'Grupo A',
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'cupo_maximo' => 10,
        ]);

        // Link group to aula via Horario
        Horario::create([
            'grupo_id' => $this->grupo->id,
            'dia_semana' => 'Lunes',
            'hora_inicio' => '07:30:00',
            'hora_fin' => '09:00:00',
            'aula' => 'Aula 101',
            'aula_id' => $this->aula->id,
        ]);

        // Create Postulante & link to Group
        $this->postulante = Postulante::create([
            'user_id' => $this->postulanteUser->id,
            'nombres_apellidos' => 'Ana Gómez',
            'ci' => '1234567',
            'carrera_primera_opcion_id' => $carrera->id,
            'gestion_id' => $gestion->id,
        ]);

        $this->postulante->grupos()->attach($this->grupo->id);
    }

    public function test_admin_can_access_visualizer()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.aulas.visualizador', ['aulaId' => $this->aula->id]));

        $response->assertStatus(200);
        $response->assertSee('Visualizador de Aula');
    }

    public function test_postulante_cannot_access_visualizer()
    {
        $response = $this->actingAs($this->postulanteUser)
            ->get(route('admin.aulas.visualizador', ['aulaId' => $this->aula->id]));

        $response->assertStatus(403);
    }

    public function test_admin_can_assign_unassign_and_clear_seats()
    {
        $this->actingAs($this->admin);

        // Verify initially unassigned
        $this->assertNull($this->postulante->grupos()->first()->pivot->nro_asiento);

        // Test Livewire component
        Livewire::test(VisualizadorAula::class, ['aulaId' => $this->aula->id])
            ->assertSet('selectedGrupoId', $this->grupo->id)
            ->call('assignSeat', $this->postulante->id, 5)
            ->assertSet('seatingMap.5.id', $this->postulante->id);

        // Verify saved in DB
        $this->assertEquals(5, $this->postulante->fresh()->grupos()->first()->pivot->nro_asiento);

        // Unassign seat
        Livewire::test(VisualizadorAula::class, ['aulaId' => $this->aula->id])
            ->call('unassignSeat', $this->postulante->id)
            ->assertSet('seatingMap.5', null);

        // Verify cleared in DB
        $this->assertNull($this->postulante->fresh()->grupos()->first()->pivot->nro_asiento);
    }

    public function test_auto_assign_remaining_seats()
    {
        $this->actingAs($this->admin);

        Livewire::test(VisualizadorAula::class, ['aulaId' => $this->aula->id])
            ->call('autoAssignRemaining');

        // Since it's the only student, they should be assigned to seat 1
        $this->assertEquals(1, $this->postulante->fresh()->grupos()->first()->pivot->nro_asiento);
    }

    public function test_auto_assign_with_various_criteria()
    {
        $this->actingAs($this->admin);

        // Add a second student who is alphabetically later, but has a higher grade
        // Ana Gómez has ID 1 and grade 70
        $this->postulante->update(['nota_final' => 70.00]);

        $student2User = User::factory()->create();
        $student2 = Postulante::create([
            'user_id' => $student2User->id,
            'nombres_apellidos' => 'Zacarías Flores', // Z is after A
            'ci' => '7654321',
            'carrera_primera_opcion_id' => $this->postulante->carrera_primera_opcion_id,
            'gestion_id' => $this->postulante->gestion_id,
            'nota_final' => 95.00, // higher grade
        ]);
        $student2->grupos()->attach($this->grupo->id);

        // 1. Test Grade Descending: Zacarías (95) first, then Ana (70)
        // Zacarías should get seat 1, Ana seat 2
        Livewire::test(VisualizadorAula::class, ['aulaId' => $this->aula->id])
            ->set('distributionCriteria', 'nota_desc')
            ->call('autoAssignRemaining');

        $this->assertEquals(2, $this->postulante->fresh()->grupos()->first()->pivot->nro_asiento);
        $this->assertEquals(1, $student2->fresh()->grupos()->first()->pivot->nro_asiento);

        // Reset seats
        $this->postulante->grupos()->updateExistingPivot($this->grupo->id, ['nro_asiento' => null]);
        $student2->grupos()->updateExistingPivot($this->grupo->id, ['nro_asiento' => null]);

        // 2. Test Grade Ascending: Ana (70) first, then Zacarías (95)
        // Ana should get seat 1, Zacarías seat 2
        Livewire::test(VisualizadorAula::class, ['aulaId' => $this->aula->id])
            ->set('distributionCriteria', 'nota_asc')
            ->call('autoAssignRemaining');

        $this->assertEquals(1, $this->postulante->fresh()->grupos()->first()->pivot->nro_asiento);
        $this->assertEquals(2, $student2->fresh()->grupos()->first()->pivot->nro_asiento);

        // Reset seats
        $this->postulante->grupos()->updateExistingPivot($this->grupo->id, ['nro_asiento' => null]);
        $student2->grupos()->updateExistingPivot($this->grupo->id, ['nro_asiento' => null]);

        // 3. Test Alphabetical Descending: Zacarías first, then Ana
        // Zacarías should get seat 1, Ana seat 2
        Livewire::test(VisualizadorAula::class, ['aulaId' => $this->aula->id])
            ->set('distributionCriteria', 'alfabetico_desc')
            ->call('autoAssignRemaining');

        $this->assertEquals(2, $this->postulante->fresh()->grupos()->first()->pivot->nro_asiento);
        $this->assertEquals(1, $student2->fresh()->grupos()->first()->pivot->nro_asiento);
    }

    public function test_clear_all_assignments()
    {
        $this->actingAs($this->admin);

        // Seat the student first
        $this->postulante->grupos()->updateExistingPivot($this->grupo->id, ['nro_asiento' => 3]);

        Livewire::test(VisualizadorAula::class, ['aulaId' => $this->aula->id])
            ->assertSet('seatingMap.3.id', $this->postulante->id)
            ->call('clearAllAssignments');

        $this->assertNull($this->postulante->fresh()->grupos()->first()->pivot->nro_asiento);
    }
}
