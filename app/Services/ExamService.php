<?php

namespace App\Services;

use App\Models\Examen;
use App\Models\Nota;
use App\Models\Postulante;
use App\Models\Materia;
use App\Exceptions\ExamValidationException;
use Illuminate\Support\Facades\DB;

class ExamService
{
    // Tipos de examen permitidos y sus ponderaciones preestablecidas
    public const PONDERACIONES_VALIDAS = [
        'Primer Parcial' => 30.00,
        'Segundo Parcial' => 30.00,
        'Examen Final' => 40.00,
    ];

    /**
     * Registra un nuevo examen validando que no sea duplicado y que cumpla la regla 30/30/40.
     *
     * @param int $materiaId
     * @param int $gestionId
     * @param string $nombre Must be one of: 'Primer Parcial', 'Segundo Parcial', 'Examen Final'
     * @param string $fecha Format: Y-m-d
     * @return Examen
     * @throws ExamValidationException
     */
    public function createExam(int $materiaId, int $gestionId, string $nombre, string $fecha): Examen
    {
        // 1. Validar nombre del examen y obtener ponderación correspondiente
        if (!array_key_exists($nombre, self::PONDERACIONES_VALIDAS)) {
            throw new ExamValidationException("El nombre del examen debe ser uno de: 'Primer Parcial', 'Segundo Parcial' o 'Examen Final'.");
        }

        $ponderacion = self::PONDERACIONES_VALIDAS[$nombre];

        // 2. Verificar que no exista un examen idéntico (materia, gestión y tipo)
        $existeDuplicado = Examen::where('materia_id', $materiaId)
            ->where('gestion_id', $gestionId)
            ->where('nombre', $nombre)
            ->exists();

        if ($existeDuplicado) {
            throw new ExamValidationException("Ya se encuentra registrado un examen '{$nombre}' para esta materia en la gestión indicada.");
        }

        // 3. Control de porcentajes: validar que la suma total no exceda el 100%
        $sumaActual = Examen::where('materia_id', $materiaId)
            ->where('gestion_id', $gestionId)
            ->sum('ponderacion');

        if (($sumaActual + $ponderacion) > 100.00) {
            throw new ExamValidationException("La ponderación total de exámenes para esta materia y gestión no puede superar el 100.00%. Ponderación actual: {$sumaActual}%.");
        }

        // 4. Crear el Examen
        return Examen::create([
            'nombre' => $nombre,
            'materia_id' => $materiaId,
            'gestion_id' => $gestionId,
            'ponderacion' => $ponderacion,
            'fecha' => $fecha,
        ]);
    }

    /**
     * Registra las notas de los postulantes para un examen y recalcula nota final de admisión.
     *
     * @param int $examenId
     * @param array $grades Array asociativo de [postulante_id => puntaje]
     * @param int|null $registradoPorId ID del usuario que registra las notas
     * @throws ExamValidationException
     */
    public function registerGrades(int $examenId, array $grades, ?int $registradoPorId = null): void
    {
        $examen = Examen::find($examenId);
        if (!$examen) {
            throw new ExamValidationException("El examen especificado no existe.");
        }

        // 1. Validar todas las notas primero
        foreach ($grades as $postulanteId => $puntaje) {
            if (!is_numeric($puntaje) || $puntaje < 0.00 || $puntaje > 100.00) {
                throw new ExamValidationException("El puntaje para el postulante ID {$postulanteId} debe estar en el rango de 0.00 a 100.00.");
            }
        }

        // 2. Procesamiento transaccional de guardado y recálculo
        DB::transaction(function () use ($examen, $grades, $registradoPorId) {
            foreach ($grades as $postulanteId => $puntaje) {
                // Registrar o actualizar nota
                Nota::updateOrCreate(
                    [
                        'postulante_id' => $postulanteId,
                        'examen_id' => $examen->id,
                    ],
                    [
                        'puntaje' => $puntaje,
                        'registrado_por' => $registradoPorId,
                    ]
                );

                // Recalcular la situación académica del postulante
                $this->recalculatePostulanteScore($postulanteId, $examen->gestion_id);
            }
        });
    }

    /**
     * Recalcula la nota final y el estado de admisión del postulante basándose en sus notas.
     *
     * @param int $postulanteId
     * @param int $gestionId
     */
    public function recalculatePostulanteScore(int $postulanteId, int $gestionId): void
    {
        $postulante = Postulante::find($postulanteId);
        if (!$postulante) {
            return;
        }

        // Obtener las materias de la carrera seleccionada por el postulante (primera opción)
        $carreraId = $postulante->carrera_primera_opcion_id;
        $materias = Materia::where('carrera_id', $carreraId)->get();

        if ($materias->isEmpty()) {
            $postulante->update([
                'nota_final' => 0.00,
                'estado_admision' => 'pendiente',
            ]);
            return;
        }

        $sumMaterias = 0.00;
        $totalMaterias = $materias->count();
        $allExamsGraded = true; // Flag para determinar si todos los exámenes de todas las materias tienen nota registrada

        foreach ($materias as $materia) {
            // Obtener los exámenes registrados para esta materia y gestión
            $examenes = Examen::where('materia_id', $materia->id)
                ->where('gestion_id', $gestionId)
                ->get();

            // Si no hay exámenes todavía creados para esta materia o no suman 100% de ponderación,
            // marcamos que no se han calificado todos los exámenes necesarios
            $totalPonderacionMateria = $examenes->sum('ponderacion');
            if ($totalPonderacionMateria < 100.00) {
                $allExamsGraded = false;
            }

            $notaMateria = 0.00;
            foreach ($examenes as $exam) {
                // Buscar la nota del postulante para este examen
                $nota = Nota::where('postulante_id', $postulante->id)
                    ->where('examen_id', $exam->id)
                    ->first();

                if ($nota) {
                    // Contribución de esta nota según ponderación del examen
                    $notaMateria += ($nota->puntaje * ($exam->ponderacion / 100.00));
                } else {
                    // Si el examen ya está registrado en el semestre pero el postulante no tiene nota,
                    // asumimos 0.00 para la ponderación y marcamos que falta calificar
                    $allExamsGraded = false;
                }
            }

            $sumMaterias += $notaMateria;
        }

        // Calcular promedio final de admisión
        $promedioFinal = $sumMaterias / $totalMaterias;

        // Determinar estado de admisión
        // El estado se mantiene en 'pendiente' si no se han completado y calificado todos los exámenes (30/30/40) para todas las materias de su carrera
        if ($allExamsGraded) {
            $nuevoEstado = $promedioFinal >= 51.00 ? 'admitido_primera_opcion' : 'reprobado';
        } else {
            $nuevoEstado = 'pendiente';
        }

        // Actualizar datos del postulante
        $postulante->update([
            'nota_final' => round($promedioFinal, 2),
            'estado_admision' => $nuevoEstado,
        ]);
    }
}
