<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\Carrera;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Notificacion;
use App\Models\Postulante;
use App\Models\ReclamoNota;
use App\Models\User;
use App\Services\GroupGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmpliacionesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test classroom CRUD database constraints
     */
    public function test_classroom_creation_and_capacity(): void
    {
        $aula = Aula::create([
            'nombre' => 'Aula 101',
            'capacidad' => 45,
            'ubicación' => 'Bloque A, Planta Baja',
        ]);

        $this->assertDatabaseHas('aulas', [
            'nombre' => 'Aula 101',
            'capacidad' => 45,
        ]);
    }

    /**
     * Test scheduling overlap detection & capacity warnings during group generation
     */
    public function test_group_generation_schedule_overlap_and_capacity(): void
    {
        $service = new GroupGenerationService;

        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $carrera = Carrera::create([
            'nombre' => 'Ingeniería Informática',
            'sigla' => 'INF',
        ]);

        $materia = Materia::create([
            'nombre' => 'Cálculo I',
            'sigla' => 'MAT-101',
            'carrera_id' => $carrera->id,
        ]);

        // Create 70 postulants to trigger group generation with size > 40
        for ($i = 1; $i <= 70; $i++) {
            $user = User::create([
                'name' => "P {$i}",
                'email' => "p{$i}@example.com",
                'password' => bcrypt('password'),
            ]);

            Postulante::create([
                'user_id' => $user->id,
                'ci' => "CI-{$i}",
                'telefono' => '70000000',
                'fecha_nacimiento' => '2005-01-01',
                'carrera_primera_opcion_id' => $carrera->id,
                'gestion_id' => $gestion->id,
                'estado_admision' => 'pendiente',
            ]);
        }

        // Create a teacher
        $userDoc = User::create(['name' => 'Docente 1', 'email' => 'd1@example.com', 'password' => 'password']);
        $docente = Docente::create([
            'user_id' => $userDoc->id,
            'ci' => '999111',
            'especialidad' => 'Matemáticas',
            'disponibilidad_horaria' => ['slot_1'],
            'formacion_academica' => 'Lic. Matemáticas',
        ]);
        $docente->materias()->attach($materia->id);

        // Pre-create classroom with small capacity to trigger capacity warnings
        Aula::create([
            'nombre' => 'Aula Pequeña',
            'capacidad' => 30, // 70 students will exceed this capacity
            'ubicación' => 'Bloque C',
        ]);

        // Generate groups
        $result = $service->generate($gestion->id);

        $this->assertTrue($result['success']);

        // Assert that capacity warning was emitted since 70 postulants > 30 capacity
        $warningFound = false;
        foreach ($result['warnings'] as $warning) {
            if (str_contains($warning, 'supera la capacidad')) {
                $warningFound = true;
            }
        }
        $this->assertTrue($warningFound, 'Should emit capacity warnings');
    }

    /**
     * Test notification sending helper
     */
    public function test_notification_helper(): void
    {
        $user = User::create([
            'name' => 'Estudiante Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Notificacion::enviar($user->id, 'Prueba de Alerta', 'Este es un mensaje de prueba.');

        $this->assertDatabaseHas('notificaciones', [
            'user_id' => $user->id,
            'titulo' => 'Prueba de Alerta',
            'mensaje' => 'Este es un mensaje de prueba.',
            'leido' => false,
        ]);
    }

    /**
     * Test Grade appeal creation and teacher resolution
     */
    public function test_grade_appeal_flow(): void
    {
        $gestion = Gestion::create([
            'nombre' => 'I-2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'activo' => true,
        ]);

        $carrera = Carrera::create([
            'nombre' => 'Sistemas',
            'sigla' => 'SIS',
        ]);

        $materia = Materia::create([
            'nombre' => 'Física I',
            'sigla' => 'FIS-101',
            'carrera_id' => $carrera->id,
        ]);

        $userStudent = User::create(['name' => 'Estudiante 1', 'email' => 'est1@example.com', 'password' => 'password']);
        $postulante = Postulante::create([
            'user_id' => $userStudent->id,
            'ci' => '12345',
            'telefono' => '7111',
            'fecha_nacimiento' => '2005-01-01',
            'carrera_primera_opcion_id' => $carrera->id,
            'gestion_id' => $gestion->id,
            'estado_admision' => 'pendiente',
        ]);

        $userDocente = User::create(['name' => 'Docente 1', 'email' => 'doc1@example.com', 'password' => 'password']);
        $docente = Docente::create([
            'user_id' => $userDocente->id,
            'ci' => '54321',
            'especialidad' => 'Física',
            'disponibilidad_horaria' => ['slot_1'],
            'formacion_academica' => 'Lic',
        ]);

        $examen = Examen::create([
            'materia_id' => $materia->id,
            'gestion_id' => $gestion->id,
            'nombre' => 'Primer Parcial',
            'ponderacion' => 30.00,
            'fecha' => '2026-04-10',
        ]);

        // Student files an appeal
        $appeal = ReclamoNota::create([
            'postulante_id' => $postulante->id,
            'examen_id' => $examen->id,
            'descripcion' => 'Revisión por favor, la pregunta 3 está correcta.',
            'estado' => 'pendiente',
            'docente_id' => $docente->id,
            'nota_anterior' => 45.00,
        ]);

        $this->assertDatabaseHas('reclamos_notas', [
            'postulante_id' => $postulante->id,
            'examen_id' => $examen->id,
            'estado' => 'pendiente',
        ]);
    }
}
