<?php

namespace Tests\Feature;

use App\Livewire\CalculadoraAdmision;
use App\Models\Carrera;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculadoraAdmisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculadora_admision_renders_successfully()
    {
        $user = User::factory()->create();
        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(CalculadoraAdmision::class)
            ->assertStatus(200)
            ->assertSee('Calculadora e Indicador de Admisión');
    }

    public function test_calculadora_admision_calculates_projected_average_and_target_needed()
    {
        $user = User::factory()->create();

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

        $examen1 = Examen::create([
            'nombre' => 'Primer Parcial',
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'ponderacion' => 50.00,
            'fecha' => now()->format('Y-m-d'),
        ]);

        $examen2 = Examen::create([
            'nombre' => 'Examen Final',
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'ponderacion' => 50.00,
            'fecha' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $postulante = Postulante::create([
            'user_id' => $user->id,
            'nombres_apellidos' => 'Juan Pérez',
            'ci' => '12345678',
            'carrera_primera_opcion_id' => $carrera->id,
            'gestion_id' => $gestion->id,
        ]);

        // Registrar nota real para examen 1 (70 pts)
        Nota::create([
            'postulante_id' => $postulante->id,
            'examen_id' => $examen1->id,
            'puntaje' => 70.00,
        ]);

        $this->actingAs($user);

        // Probar componente Livewire
        $component = Livewire::test(CalculadoraAdmision::class)
            ->assertSet('postulante.id', $postulante->id)
            ->set('targetScore', 60.00)
            ->call('calcularObjetivo');

        // Examen 1 (70 * 0.5 = 35 pts ya ganados).
        // Para llegar a 60 en total en la materia, faltan 25 pts ponderados en Examen 2 (50% ponderación).
        // 25 / 0.5 = 50 pts necesarios en Examen 2.
        $materiasData = $component->get('materiasData');
        $exam2Simulated = $materiasData[0]['examenes'][1]['nota_simulada'];

        $this->assertEquals(50.00, $exam2Simulated);
        $this->assertEquals(60.00, $component->get('promedioProyectado'));
    }
}
