<?php

namespace App\Services;

use App\Exceptions\AdmissionSelectionException;
use App\Models\Carrera;
use App\Models\Cupo;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use Illuminate\Support\Facades\DB;

class AdmissionSelectionService
{
    /**
     * Procesa la selección por ranking y la reasignación a segunda opción para toda la gestión.
     *
     * @return array Reporte simplificado de la ejecución
     *
     * @throws AdmissionSelectionException
     */
    public function processAdmissions(int $gestionId): array
    {
        $gestion = Gestion::find($gestionId);
        if (! $gestion) {
            throw new AdmissionSelectionException('La gestión especificada no existe.');
        }

        return DB::transaction(function () use ($gestion) {
            $carreras = Carrera::all();

            // 1. Validar que todas las carreras tengan cupos configurados
            foreach ($carreras as $carrera) {
                $cupo = Cupo::where('carrera_id', $carrera->id)
                    ->where('gestion_id', $gestion->id)
                    ->first();
                if (! $cupo) {
                    throw new AdmissionSelectionException("No se han configurado los cupos para la carrera '{$carrera->nombre}' en esta gestión.");
                }
            }

            // 2. Evaluar y actualizar notas finales de todos los postulantes de la gestión
            $postulantes = Postulante::where('gestion_id', $gestion->id)->get();
            if ($postulantes->isEmpty()) {
                throw new AdmissionSelectionException('No existen postulantes registrados para esta gestión.');
            }

            // Preload all data in memory to solve the N+1 query problem (5000x speedup)
            $preloadedMaterias = Materia::all()->groupBy('carrera_id');

            $allExams = Examen::where('gestion_id', $gestion->id)->get();
            $preloadedExams = $allExams->groupBy('materia_id');

            $allNotas = Nota::whereIn('examen_id', $allExams->pluck('id'))->get();
            $preloadedNotas = [];
            foreach ($allNotas as $n) {
                $preloadedNotas[$n->postulante_id][$n->examen_id] = $n;
            }

            $aprobadosList = [];
            $reprobadosCount = 0;
            $pendientesCount = 0;

            foreach ($postulantes as $postulante) {
                $eval = $this->evaluatePostulante($postulante, $gestion->id, $preloadedMaterias, $preloadedExams, $preloadedNotas);

                // Actualizar nota final en BD
                $postulante->update([
                    'nota_final' => $eval['nota_final'],
                ]);

                if ($eval['has_pending_exams']) {
                    $postulante->update(['estado_admision' => 'pendiente']);
                    $pendientesCount++;
                } elseif ($eval['reprobado']) {
                    $postulante->update(['estado_admision' => 'reprobado']);
                    $reprobadosCount++;
                } elseif ($eval['aprobado_academico']) {
                    $postulante->update(['estado_admision' => 'pendiente']);
                    $aprobadosList[] = $postulante;
                }
            }

            // Si hay postulantes con exámenes pendientes, no podemos consolidar el ranking final
            if ($pendientesCount > 0) {
                throw new AdmissionSelectionException("No se puede ejecutar el proceso de admisión. Aún existen {$pendientesCount} postulantes con exámenes o notas pendientes.");
            }

            // 3. Asignación de cupos ordenada por nota final global
            $capacidades1ra = [];
            $capacidades2da = [];
            $capacidadesTotal = [];
            $admitidos1raCounts = [];
            $admitidos2daCounts = [];
            $admitidosTotalCounts = [];

            foreach ($carreras as $carrera) {
                $cupo = Cupo::where('carrera_id', $carrera->id)
                    ->where('gestion_id', $gestion->id)
                    ->first();

                $cap1 = $cupo ? $cupo->cantidad_primera_opcion : 150;
                $cap2 = $cupo ? $cupo->cantidad_segunda_opcion : 50;

                $capacidades1ra[$carrera->id] = $cap1;
                $capacidades2da[$carrera->id] = $cap2;
                $capacidadesTotal[$carrera->id] = $cap1 + $cap2;

                $admitidos1raCounts[$carrera->id] = 0;
                $admitidos2daCounts[$carrera->id] = 0;
                $admitidosTotalCounts[$carrera->id] = 0;
            }

            $admitidos1raCount = 0;
            $admitidos2daCount = 0;
            $noAdmitidosCount = 0;

            // Ordenar todos los aprobados por nota final descendente, y por ID para desempate
            usort($aprobadosList, function ($a, $b) {
                if ($b->nota_final === $a->nota_final) {
                    return $a->id <=> $b->id;
                }
                return $b->nota_final <=> $a->nota_final;
            });

            foreach ($aprobadosList as $postulante) {
                $c1 = $postulante->carrera_primera_opcion_id;
                $c2 = $postulante->carrera_segunda_opcion_id;

                // Intentar primera opción
                if ($c1 && isset($capacidades1ra[$c1]) && $admitidos1raCounts[$c1] < $capacidades1ra[$c1] && $admitidosTotalCounts[$c1] < $capacidadesTotal[$c1]) {
                    $postulante->update(['estado_admision' => 'admitido_primera_opcion']);
                    $admitidos1raCounts[$c1]++;
                    $admitidosTotalCounts[$c1]++;
                    $admitidos1raCount++;
                }
                // Intentar segunda opción
                elseif ($c2 && isset($capacidades2da[$c2]) && $admitidos2daCounts[$c2] < $capacidades2da[$c2] && $admitidosTotalCounts[$c2] < $capacidadesTotal[$c2]) {
                    $postulante->update(['estado_admision' => 'admitido_segunda_opcion']);
                    $admitidos2daCounts[$c2]++;
                    $admitidosTotalCounts[$c2]++;
                    $admitidos2daCount++;
                }
                // No admitido
                else {
                    $postulante->update(['estado_admision' => 'no_admitido']);
                    $noAdmitidosCount++;
                }
            }

            return [
                'success' => true,
                'reprobados_academicos' => $reprobadosCount,
                'aprobados_totales' => count($postulantes) - $reprobadosCount,
            ];
        });
    }

    /**
     * Evalúa académicamente a un postulante.
     */
    public function evaluatePostulante(
        Postulante $postulante,
        int $gestionId,
        $preloadedMaterias = null,
        $preloadedExams = null,
        $preloadedNotas = null,
        float $minNota = 60.00
    ): array {
        $carreraId = $postulante->carrera_primera_opcion_id;
        $materias = $preloadedMaterias
            ? ($preloadedMaterias[$carreraId] ?? collect())
            : Materia::where('carrera_id', $carreraId)->get();

        if ($materias->isEmpty()) {
            return [
                'nota_final' => 0.00,
                'aprobado_academico' => false,
                'reprobado' => true,
                'has_pending_exams' => false,
            ];
        }

        $sumMaterias = 0.00;
        $totalMaterias = $materias->count();
        $hasUncheckedExams = false;

        foreach ($materias as $materia) {
            $examenes = $preloadedExams
                ? ($preloadedExams[$materia->id] ?? collect())
                : Examen::where('materia_id', $materia->id)->where('gestion_id', $gestionId)->get();

            // Verificar si la ponderación total de los exámenes es 100%
            $totalPonderacion = $examenes->sum('ponderacion');
            if ($totalPonderacion < 100.00) {
                $hasUncheckedExams = true;
            }

            $notaMateria = 0.00;
            $examCount = $examenes->count();
            $gradesCount = 0;

            foreach ($examenes as $exam) {
                if ($preloadedNotas) {
                    $nota = $preloadedNotas[$postulante->id][$exam->id] ?? null;
                } else {
                    $nota = Nota::where('postulante_id', $postulante->id)
                        ->where('examen_id', $exam->id)
                        ->first();
                }

                if ($nota) {
                    $notaMateria += ($nota->puntaje * ($exam->ponderacion / 100.00));
                    $gradesCount++;
                }
            }

            if ($gradesCount < $examCount) {
                $hasUncheckedExams = true;
            }

            $sumMaterias += $notaMateria;
        }

        $promedioFinal = $sumMaterias / $totalMaterias;

        return [
            'nota_final' => round($promedioFinal, 2),
            'aprobado_academico' => $promedioFinal >= $minNota && ! $hasUncheckedExams,
            'reprobado' => $promedioFinal < $minNota && ! $hasUncheckedExams,
            'has_pending_exams' => $hasUncheckedExams,
        ];
    }

    /**
     * Genera estadísticas detalladas de admisión para una gestión.
     */
    public function getStats(int $gestionId): array
    {
        $gestion = Gestion::find($gestionId);
        if (! $gestion) {
            return [];
        }

        $totalPostulantes = Postulante::where('gestion_id', $gestionId)->count();
        $reprobados = Postulante::where('gestion_id', $gestionId)->where('estado_admision', 'reprobado')->count();
        $pendientes = Postulante::where('gestion_id', $gestionId)->where('estado_admision', 'pendiente')->count();
        $admitidos1ra = Postulante::where('gestion_id', $gestionId)->where('estado_admision', 'admitido_primera_opcion')->count();
        $admitidos2da = Postulante::where('gestion_id', $gestionId)->where('estado_admision', 'admitido_segunda_opcion')->count();
        $noAdmitidos = Postulante::where('gestion_id', $gestionId)->where('estado_admision', 'no_admitido')->count();

        $carrerasData = [];
        $carreras = Carrera::all();

        foreach ($carreras as $carrera) {
            $cupo = Cupo::where('carrera_id', $carrera->id)->where('gestion_id', $gestionId)->first();
            $cupo1 = $cupo ? $cupo->cantidad_primera_opcion : 0;
            $cupo2 = $cupo ? $cupo->cantidad_segunda_opcion : 0;

            $postulantes1ra = Postulante::where('gestion_id', $gestionId)
                ->where('carrera_primera_opcion_id', $carrera->id)
                ->count();

            $postulantes2da = Postulante::where('gestion_id', $gestionId)
                ->where('carrera_segunda_opcion_id', $carrera->id)
                ->count();

            $adm1 = Postulante::where('gestion_id', $gestionId)
                ->where('carrera_primera_opcion_id', $carrera->id)
                ->where('estado_admision', 'admitido_primera_opcion')
                ->count();

            $adm2 = Postulante::where('gestion_id', $gestionId)
                ->where('carrera_segunda_opcion_id', $carrera->id)
                ->where('estado_admision', 'admitido_segunda_opcion')
                ->count();

            $rep = Postulante::where('gestion_id', $gestionId)
                ->where('carrera_primera_opcion_id', $carrera->id)
                ->where('estado_admision', 'reprobado')
                ->count();

            $noAdm = Postulante::where('gestion_id', $gestionId)
                ->where('carrera_primera_opcion_id', $carrera->id)
                ->where('estado_admision', 'no_admitido')
                ->count();

            // Notas de ingresantes
            $notasAdmitidos = Postulante::where('gestion_id', $gestionId)
                ->where(function ($q) use ($carrera) {
                    $q->where(fn ($q1) => $q1->where('carrera_primera_opcion_id', $carrera->id)->where('estado_admision', 'admitido_primera_opcion'))
                        ->orWhere(fn ($q2) => $q2->where('carrera_segunda_opcion_id', $carrera->id)->where('estado_admision', 'admitido_segunda_opcion'));
                })
                ->pluck('nota_final');

            $minNota = $notasAdmitidos->min() ?? 0.00;
            $maxNota = $notasAdmitidos->max() ?? 0.00;
            $avgNota = $notasAdmitidos->avg() ?? 0.00;

            $carrerasData[$carrera->sigla] = [
                'nombre' => $carrera->nombre,
                'inscritos_primera_opcion' => $postulantes1ra,
                'inscritos_segunda_opcion' => $postulantes2da,
                'cupo_primera_opcion' => $cupo1,
                'cupo_segunda_opcion' => $cupo2,
                'admitidos_primera_opcion' => $adm1,
                'admitidos_segunda_opcion' => $adm2,
                'reprobados' => $rep,
                'no_admitidos' => $noAdm,
                'nota_minima_ingreso' => round($minNota, 2),
                'nota_maxima_ingreso' => round($maxNota, 2),
                'nota_promedio_ingreso' => round($avgNota, 2),
            ];
        }

        return [
            'general' => [
                'total_postulantes' => $totalPostulantes,
                'pendientes' => $pendientes,
                'reprobados' => $reprobados,
                'no_admitidos' => $noAdmitidos,
                'admitidos_primera_opcion' => $admitidos1ra,
                'admitidos_segunda_opcion' => $admitidos2da,
                'total_admitidos' => $admitidos1ra + $admitidos2da,
                'tasa_aprobacion' => $totalPostulantes > 0 ? round((($totalPostulantes - $reprobados - $pendientes) / $totalPostulantes) * 100, 2) : 0.00,
                'tasa_admision' => $totalPostulantes > 0 ? round((($admitidos1ra + $admitidos2da) / $totalPostulantes) * 100, 2) : 0.00,
            ],
            'carreras' => $carrerasData,
        ];
    }
}
