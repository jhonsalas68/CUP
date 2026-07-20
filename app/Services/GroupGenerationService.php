<?php

namespace App\Services;

use App\Models\Gestion;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Grupo;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Postulante;
use App\Models\Examen;
use App\Exceptions\GroupGenerationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupGenerationService
{
    // Parámetros de capacidad y divisor para conformación de grupos (examen)
    public const ALUMNOS_DIVISOR = 70; // Divisor de la fórmula (CEIL(TotalInscritos / 70))
    public const CUPO_MAXIMO_GRUPO = 70; // Capacidad física máxima por grupo
    public const MAX_GRUPOS_POR_DOCENTE = 4; // Límite máximo de grupos asignados por docente en una gestión

    // Pool de slots horarios preestablecidos (8 slots)
    // Cada slot representa 2 sesiones semanales de 1.5 horas cada una.
    public const SLOTS = [
        'slot_1' => [
            'nombre' => 'Lunes y Miércoles 07:30 - 09:00',
            'dias' => ['lunes', 'miercoles'],
            'hora_inicio' => '07:30:00',
            'hora_fin' => '09:00:00',
        ],
        'slot_2' => [
            'nombre' => 'Lunes y Miércoles 09:15 - 10:45',
            'dias' => ['lunes', 'miercoles'],
            'hora_inicio' => '09:15:00',
            'hora_fin' => '10:45:00',
        ],
        'slot_3' => [
            'nombre' => 'Lunes y Miércoles 11:00 - 12:30',
            'dias' => ['lunes', 'miercoles'],
            'hora_inicio' => '11:00:00',
            'hora_fin' => '12:30:00',
        ],
        'slot_4' => [
            'nombre' => 'Martes y Jueves 07:30 - 09:00',
            'dias' => ['martes', 'jueves'],
            'hora_inicio' => '07:30:00',
            'hora_fin' => '09:00:00',
        ],
        'slot_5' => [
            'nombre' => 'Martes y Jueves 09:15 - 10:45',
            'dias' => ['martes', 'jueves'],
            'hora_inicio' => '09:15:00',
            'hora_fin' => '10:45:00',
        ],
        'slot_6' => [
            'nombre' => 'Martes y Jueves 11:00 - 12:30',
            'dias' => ['martes', 'jueves'],
            'hora_inicio' => '11:00:00',
            'hora_fin' => '12:30:00',
        ],
        'slot_7' => [
            'nombre' => 'Viernes y Sábado 07:30 - 09:00',
            'dias' => ['viernes', 'sabado'],
            'hora_inicio' => '07:30:00',
            'hora_fin' => '09:00:00',
        ],
        'slot_8' => [
            'nombre' => 'Viernes y Sábado 09:15 - 10:45',
            'dias' => ['viernes', 'sabado'],
            'hora_inicio' => '09:15:00',
            'hora_fin' => '10:45:00',
        ],
    ];

    // Pool de aulas físicas disponibles
    public const AULAS = [
        'Aula 101', 'Aula 102', 'Aula 103', 'Aula 201', 'Aula 202', 'Aula 203',
        'Lab de Computación A', 'Lab de Computación B', 'Auditorio Civil'
    ];

    /**
     * Orquesta la generación automática de grupos para una gestión específica.
     *
     * @param int $gestionId
     * @return array Reporte de resultados y advertencias
     * @throws GroupGenerationException
     */
    public function generate(int $gestionId): array
    {
        // 1. Validar la Gestión
        $gestion = Gestion::find($gestionId);
        if (!$gestion) {
            throw new GroupGenerationException("La gestión especificada no existe.");
        }

        // 2. Ejecutar todo dentro de una transacción para consistencia total
        return DB::transaction(function () use ($gestion) {
            $warnings = [];
            $stats = [
                'grupos_creados' => 0,
                'alumnos_asignados' => 0,
                'materias_procesadas' => 0,
            ];

            // 3. Limpieza de grupos previos (si aplica y no hay exámenes registrados)
            $this->cleanExistingGroups($gestion->id);

            // 4. Obtener Carreras y Postulantes
            $carreras = Carrera::all();
            
            // Seguimiento en memoria para evitar cruces durante esta ejecución
            // Estructura: $busyTeachers[$docenteId][$slotKey] = true
            $busyTeachers = [];
            
            // Estructura: $occupiedClassrooms[$slotKey][] = $aulaName
            $occupiedClassrooms = [];

            // Cargar ocupación de docentes ya registrada en la base de datos para esta gestión
            $this->loadExistingTeacherOccupancy($gestion->id, $busyTeachers);
            
            // Cargar ocupación de aulas ya registrada en la base de datos para esta gestión
            $this->loadExistingClassroomOccupancy($gestion->id, $occupiedClassrooms);

            foreach ($carreras as $carrera) {
                // Obtener postulantes de esta carrera y gestión (primera opción)
                $postulantes = Postulante::where('carrera_primera_opcion_id', $carrera->id)
                    ->where('gestion_id', $gestion->id)
                    ->get();

                if ($postulantes->isEmpty()) {
                    continue;
                }

                // Obtener materias de la carrera
                $materias = Materia::where('carrera_id', $carrera->id)->get();
                if ($materias->isEmpty()) {
                    $warnings[] = "La carrera '{$carrera->nombre}' no tiene materias configuradas.";
                    continue;
                }

                // Procesar cada materia asignando slots horarios diferentes
                foreach ($materias as $index => $materia) {
                    $totalAlumnos = $postulantes->count();
                    // Límite de alumnos por grupo según divisor parametrizado
                    $numGrupos = (int) ceil($totalAlumnos / self::ALUMNOS_DIVISOR);

                    // Distribución equitativa de alumnos
                    $chunks = $this->distributeEquatively($postulantes, $numGrupos);

                    foreach ($chunks as $gIdx => $chunk) {
                        $groupNumber = $gIdx + 1;
                        $grupoNombre = "{$materia->sigla} - G{$groupNumber}";

                        // Selección de slot horario por rotación (materia + grupo) para evitar cruces en el docente
                        $slotIndex = ($index + $gIdx) % 8;
                        $slotKey = 'slot_' . ($slotIndex + 1);
                        $slot = self::SLOTS[$slotKey];

                        // Crear el grupo
                        $grupo = Grupo::create([
                            'nombre' => $grupoNombre,
                            'materia_id' => $materia->id,
                            'gestion_id' => $gestion->id,
                            'cupo_maximo' => self::CUPO_MAXIMO_GRUPO,
                        ]);

                        $stats['grupos_creados']++;

                        // Asociar los postulantes al grupo
                        $postulanteIds = $chunk->pluck('id')->toArray();
                        $grupo->postulantes()->attach($postulanteIds);
                        $stats['alumnos_asignados'] += count($postulanteIds);

                        // Asignación de Docente Calificado
                        $docenteAsignado = $this->assignTeacher(
                            $materia,
                            $gestion->id,
                            $slotKey,
                            $busyTeachers,
                            $grupo
                        );

                        if (!$docenteAsignado) {
                            $warnings[] = "Grupo '{$grupoNombre}' ({$materia->nombre}): No se encontró docente disponible calificado para el slot '{$slot['nombre']}'.";
                        }

                        // Asignación de Aula Disponible
                        $aulaAsignada = $this->assignClassroom(
                            $slotKey,
                            $occupiedClassrooms,
                            $grupo,
                            $slot,
                            count($postulanteIds),
                            $warnings
                        );

                        if (!$aulaAsignada) {
                            $warnings[] = "Grupo '{$grupoNombre}' ({$materia->nombre}): No hay aulas disponibles libres en el slot '{$slot['nombre']}'.";
                        }
                    }

                    $stats['materias_procesadas']++;
                }
            }

            if ($stats['grupos_creados'] === 0) {
                throw new GroupGenerationException("No se generó ningún grupo. Verifique que existan postulantes inscritos y materias para esta gestión.");
            }

            return [
                'success' => true,
                'stats' => $stats,
                'warnings' => $warnings,
            ];
        });
    }

    /**
     * Limpia grupos previos para la gestión si es seguro.
     */
    private function cleanExistingGroups(int $gestionId): void
    {
        $groupIds = Grupo::where('gestion_id', $gestionId)->pluck('id');
        if ($groupIds->isEmpty()) {
            return;
        }

        // Si ya existen exámenes registrados en este semestre, bloqueamos la regeneración
        $hasExams = Examen::where('gestion_id', $gestionId)->exists();
        if ($hasExams) {
            throw new GroupGenerationException("No se pueden regenerar los grupos. Ya existen exámenes registrados en esta gestión.");
        }

        // Limpieza segura en cascada
        DB::table('postulante_grupo')->whereIn('grupo_id', $groupIds)->delete();
        DB::table('asignaciones_docente')->whereIn('grupo_id', $groupIds)->delete();
        DB::table('horarios')->whereIn('grupo_id', $groupIds)->delete();
        
        // Eliminación física o lógica
        Grupo::whereIn('id', $groupIds)->forceDelete();
    }

    /**
     * Carga ocupaciones existentes de docentes en la base de datos.
     */
    private function loadExistingTeacherOccupancy(int $gestionId, array &$busyTeachers): void
    {
        $horarios = Horario::whereHas('grupo', function ($q) use ($gestionId) {
            $q->where('gestion_id', $gestionId);
        })->get();

        foreach ($horarios as $horario) {
            $grupo = $horario->grupo;
            if (!$grupo) continue;

            $docentes = $grupo->docentes;
            foreach ($docentes as $docente) {
                $slotKey = $this->findSlotKeyForHorario($horario);
                if ($slotKey) {
                    $busyTeachers[$docente->id][$slotKey] = true;
                }
            }
        }
    }

    /**
     * Carga ocupaciones de aulas existentes en la base de datos.
     */
    private function loadExistingClassroomOccupancy(int $gestionId, array &$occupiedClassrooms): void
    {
        $horarios = Horario::whereHas('grupo', function ($q) use ($gestionId) {
            $q->where('gestion_id', $gestionId);
        })->whereNotNull('aula')->get();

        foreach ($horarios as $horario) {
            $slotKey = $this->findSlotKeyForHorario($horario);
            if ($slotKey) {
                $occupiedClassrooms[$slotKey][] = $horario->aula;
                if ($horario->aula_id) {
                    $occupiedClassrooms[$slotKey][] = $horario->aula_id;
                }
            }
        }
    }

    /**
     * Identifica a qué slot preestablecido pertenece un horario de la BD.
     */
    private function findSlotKeyForHorario(Horario $horario): ?string
    {
        foreach (self::SLOTS as $key => $slot) {
            if (in_array(strtolower($horario->dia_semana), $slot['dias'])) {
                // Simplificación: comparar horas de inicio y fin
                if (substr($horario->hora_inicio, 0, 5) === substr($slot['hora_inicio'], 0, 5)) {
                    return $key;
                }
            }
        }
        return null;
    }

    /**
     * Distribuye una lista de postulantes equitativamente en N grupos.
     */
    private function distributeEquatively($postulantes, int $numGrupos): array
    {
        $total = $postulantes->count();
        if ($total === 0) {
            return [];
        }

        $chunks = [];
        $baseSize = (int) floor($total / $numGrupos);
        $remainder = $total % $numGrupos;

        $offset = 0;
        for ($i = 0; $i < $numGrupos; $i++) {
            $take = $baseSize + ($i < $remainder ? 1 : 0);
            $chunks[] = $postulantes->slice($offset, $take);
            $offset += $take;
        }

        return $chunks;
    }

    /**
     * Asigna un docente calificado disponible y balancea la carga de grupos asignados.
     */
    private function assignTeacher(Materia $materia, int $gestionId, string $slotKey, array &$busyTeachers, Grupo $grupo): bool
    {
        // 1. Obtener docentes registrados para impartir esta materia
        $candidatos = $materia->docentes()->get();
        if ($candidatos->isEmpty()) {
            return false;
        }

        $elegibles = [];

        foreach ($candidatos as $docente) {
            // Verificar disponibilidad horaria del docente
            $disponibilidad = $docente->disponibilidad_horaria ?? [];
            if (!in_array($slotKey, $disponibilidad)) {
                continue; // No está disponible en este slot
            }

            // Verificar si cumple los requisitos académicos de la facultad:
            // Profesional en el área, maestría y diplomado en educación superior
            if (!$docente->profesional_area || !$docente->tiene_maestria || !$docente->tiene_diplomado) {
                continue; // No cumple con los requisitos de contratación
            }

            // Verificar si ya tiene un cruce de horario en este slot
            if (isset($busyTeachers[$docente->id][$slotKey])) {
                continue; // Ya está ocupado en este slot
            }

            // Calcular su carga actual (número de asignaciones en esta gestión)
            $carga = DB::table('asignaciones_docente')
                ->join('grupos', 'asignaciones_docente.grupo_id', '=', 'grupos.id')
                ->where('grupos.gestion_id', $gestionId)
                ->where('asignaciones_docente.docente_id', $docente->id)
                ->count();

            // Bloquear la asignación si ya alcanzó el máximo de 4 grupos activos en esta gestión
            if ($carga >= self::MAX_GRUPOS_POR_DOCENTE) {
                continue;
            }

            $elegibles[] = [
                'docente' => $docente,
                'carga' => $carga,
            ];
        }

        if (empty($elegibles)) {
            return false;
        }

        // Ordenar por menor carga (balanceo de carga)
        usort($elegibles, function ($a, $b) {
            return $a['carga'] <=> $b['carga'];
        });

        $docenteElegido = $elegibles[0]['docente'];

        // Registrar asignación
        $grupo->docentes()->attach($docenteElegido->id);

        // Bloquear al docente en este slot para evitar cruces
        $busyTeachers[$docenteElegido->id][$slotKey] = true;

        return true;
    }

    /**
     * Busca y asigna un aula libre en el slot indicado.
     */
    private function assignClassroom(string $slotKey, array &$occupiedClassrooms, Grupo $grupo, array $slot, int $studentCount, array &$warnings): bool
    {
        $aulaElegida = null;

        // Cargar aulas de la BD (con fallback automático si está vacío)
        $aulas = \App\Models\Aula::all();
        if ($aulas->isEmpty()) {
            foreach (self::AULAS as $aulaName) {
                \App\Models\Aula::create([
                    'nombre' => $aulaName,
                    'capacidad' => 70,
                    'ubicacion' => 'Pabellón Central'
                ]);
            }
            $aulas = \App\Models\Aula::all();
        }

        // Buscar la primera aula libre
        foreach ($aulas as $aula) {
            $ocupada = isset($occupiedClassrooms[$slotKey]) && (
                in_array($aula->id, $occupiedClassrooms[$slotKey]) || 
                in_array($aula->nombre, $occupiedClassrooms[$slotKey])
            );
            if (!$ocupada) {
                $aulaElegida = $aula;
                break;
            }
        }

        $aulaName = $aulaElegida ? $aulaElegida->nombre : null;
        $aulaId = $aulaElegida ? $aulaElegida->id : null;

        // Crear las sesiones en la tabla horarios (2 días a la semana según el slot)
        foreach ($slot['dias'] as $dia) {
            Horario::create([
                'grupo_id' => $grupo->id,
                'dia_semana' => $dia,
                'hora_inicio' => $slot['hora_inicio'],
                'hora_fin' => $slot['hora_fin'],
                'aula' => $aulaName,
                'aula_id' => $aulaId,
            ]);
        }

        if ($aulaElegida) {
            if ($studentCount > $aulaElegida->capacidad) {
                $warnings[] = "Grupo '{$grupo->nombre}' ({$grupo->materia->nombre}): La cantidad de alumnos asignados ({$studentCount}) supera la capacidad del Aula '{$aulaElegida->nombre}' (Capacidad: {$aulaElegida->capacidad}).";
            }
            // Marcar el aula como ocupada en este slot (guardamos tanto ID como nombre)
            $occupiedClassrooms[$slotKey][] = $aulaElegida->id;
            $occupiedClassrooms[$slotKey][] = $aulaElegida->nombre;
            return true;
        }

        return false;
    }
}
