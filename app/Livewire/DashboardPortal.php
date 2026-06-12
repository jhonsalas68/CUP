<?php

namespace App\Livewire;

use App\Models\Carrera;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Grupo;
use App\Models\Nota;
use App\Models\Postulante;
use App\Services\ExamService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class DashboardPortal extends Component
{
    use WithFileUploads;

    // Common
    public $role = '';
    
    // Postulante Profile Registration Form
    public $ci = '';
    public $telefono = '';
    public $fecha_nacimiento = '';
    public $sexo = '';
    public $direccion = '';
    public $colegio_procedencia = '';
    public $ciudad = '';
    public $carrera_primera_opcion_id = '';
    public $carrera_segunda_opcion_id = '';
    public $carrerasDisponibles = [];
    public $selectedGroups = []; // [materia_id => grupo_id]

    // Docente Grading Form
    public $selectedGrupoId = null;
    public $selectedExamenTipo = 'Primer Parcial'; // 'Primer Parcial', 'Segundo Parcial', 'Examen Final'
    public $gradesInput = []; // [postulante_id => score]
    public $fechaExamen = '';
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $user = auth()->user();
        
        if ($user->hasRole('Postulante')) {
            $this->role = 'Postulante';
            $this->carrerasDisponibles = Carrera::orderBy('nombre')->get();
            
            // Pre-fill form if profile already exists but we want to know
            $postulante = $user->postulante;
            if ($postulante) {
                $this->ci = $postulante->ci;
                $this->telefono = $postulante->telefono;
                $this->fecha_nacimiento = $postulante->fecha_nacimiento?->format('Y-m-d');
                $this->sexo = $postulante->sexo;
                $this->direccion = $postulante->direccion;
                $this->colegio_procedencia = $postulante->colegio_procedencia;
                $this->ciudad = $postulante->ciudad;
                $this->carrera_primera_opcion_id = $postulante->carrera_primera_opcion_id;
                $this->carrera_segunda_opcion_id = $postulante->carrera_segunda_opcion_id;
            }
        } elseif ($user->hasRole('Docente')) {
            $this->role = 'Docente';
            $this->fechaExamen = today()->format('Y-m-d');
        } else {
            // Check if user has other roles
            $this->role = $user->roles->pluck('name')->first() ?? 'Usuario';
            $this->carrerasDisponibles = Carrera::orderBy('nombre')->get();
        }
    }

    /**
     * Registers a new postulant profile for the logged in user
     */
    public function registerPostulante()
    {
        $user = auth()->user();
        
        $this->validate([
            'ci' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|string|in:M,F',
            'direccion' => 'required|string|max:255',
            'colegio_procedencia' => 'required|string|max:255',
            'ciudad' => 'required|string|max:100',
            'carrera_primera_opcion_id' => 'required|exists:carreras,id',
            'carrera_segunda_opcion_id' => 'nullable|exists:carreras,id|different:carrera_primera_opcion_id',
        ], [
            'carrera_segunda_opcion_id.different' => 'La segunda opción de carrera debe ser diferente a la primera.',
        ]);

        $activeGestion = Gestion::where('activo', true)->first();
        if (!$activeGestion) {
            $this->errorMessage = 'No hay una gestión académica activa en este momento para el registro.';
            return;
        }

        // Check if CI already exists for another student in the same gestion
        $ciExists = Postulante::where('ci', $this->ci)
            ->where('gestion_id', $activeGestion->id)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($ciExists) {
            $this->errorMessage = 'El documento de identidad (CI) ingresado ya está registrado para otro postulante en esta gestión.';
            return;
        }

        DB::transaction(function () use ($user, $activeGestion) {
            Postulante::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'ci' => $this->ci,
                    'telefono' => $this->telefono,
                    'fecha_nacimiento' => $this->fecha_nacimiento,
                    'sexo' => $this->sexo,
                    'direccion' => $this->direccion,
                    'colegio_procedencia' => $this->colegio_procedencia,
                    'ciudad' => $this->ciudad,
                    'carrera_primera_opcion_id' => $this->carrera_primera_opcion_id,
                    'carrera_segunda_opcion_id' => $this->carrera_segunda_opcion_id ?: null,
                    'gestion_id' => $activeGestion->id,
                    'estado_admision' => 'pendiente',
                ]
            );
        });

        $this->successMessage = '¡Tu perfil de postulante ha sido registrado correctamente!';
        $this->dispatch('profile-registered');
    }

    /**
     * Enrolls the applicant into selected groups
     */
    public function enroll()
    {
        $user = auth()->user();
        $postulante = $user->postulante;
        
        if (!$postulante) {
            $this->errorMessage = 'No tienes perfil de postulante.';
            return;
        }
        
        if (!$postulante->pago_realizado) {
            $this->errorMessage = 'Debes pagar tu inscripción para poder inscribirte a las materias.';
            return;
        }
        
        if (!$postulante->habilitado) {
            $this->errorMessage = 'Tu perfil debe ser habilitado por el administrador.';
            return;
        }

        $activeGestion = Gestion::where('activo', true)->first();
        if (!$activeGestion) {
            $this->errorMessage = 'No hay una gestión activa en este momento.';
            return;
        }

        // Fetch career materias
        $carrera = Carrera::with('materias')->find($postulante->carrera_primera_opcion_id);
        $materias = $carrera ? $carrera->materias : collect();
        
        if ($materias->isEmpty()) {
            $this->errorMessage = 'No hay materias configuradas para tu carrera.';
            return;
        }

        // Validate that they selected a group for EVERY materia
        foreach ($materias as $materia) {
            if (empty($this->selectedGroups[$materia->id])) {
                $this->errorMessage = "Por favor selecciona un grupo para la materia: {$materia->nombre}.";
                return;
            }
        }

        // Validate cupos and check each group
        $groupsToAttach = [];
        foreach ($materias as $materia) {
            $grupoId = $this->selectedGroups[$materia->id];
            $grupo = Grupo::where('materia_id', $materia->id)
                ->where('gestion_id', $activeGestion->id)
                ->find($grupoId);

            if (!$grupo) {
                $this->errorMessage = "El grupo seleccionado para la materia {$materia->nombre} no es válido.";
                return;
            }

            // Check cupo
            $currentCount = $grupo->postulantes()->count();
            if ($currentCount >= $grupo->cupo_maximo) {
                $this->errorMessage = "El grupo {$grupo->nombre} de la materia {$materia->nombre} ya no tiene cupos disponibles.";
                return;
            }

            $groupsToAttach[] = $grupo->id;
        }

        // Enroll student
        DB::transaction(function () use ($postulante, $groupsToAttach) {
            // Attach groups
            $postulante->grupos()->sync($groupsToAttach);
        });

        $this->successMessage = '¡Inscripción a materias y grupos realizada con éxito!';
        $this->dispatch('enrolled');
    }

    /**
     * Select a group to view students and enter grades
     */
    public function selectGrupo($grupoId)
    {
        $this->selectedGrupoId = $grupoId;
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->loadGrades();
    }

    /**
     * Change the exam type to grade
     */
    public function updatedSelectedExamenTipo()
    {
        $this->loadGrades();
    }

    /**
     * Load existing grades for the selected group and exam
     */
    public function loadGrades()
    {
        if (!$this->selectedGrupoId) return;

        $grupo = Grupo::with(['materia', 'postulantes'])->find($this->selectedGrupoId);
        if (!$grupo) return;

        // Find or create exam for this group, subject, active gestion
        $examen = Examen::where('materia_id', $grupo->materia_id)
            ->where('gestion_id', $grupo->gestion_id)
            ->where('nombre', $this->selectedExamenTipo)
            ->first();

        $this->gradesInput = [];
        if ($examen) {
            $studentIds = $grupo->postulantes->pluck('id')->toArray();
            $notas = Nota::whereIn('postulante_id', $studentIds)
                ->where('examen_id', $examen->id)
                ->pluck('puntaje', 'postulante_id');

            foreach ($grupo->postulantes as $student) {
                $this->gradesInput[$student->id] = $notas->get($student->id, '');
            }
        } else {
            foreach ($grupo->postulantes as $student) {
                $this->gradesInput[$student->id] = '';
            }
        }
    }

    /**
     * Saves the entered student grades
     */
    public function saveGrades(ExamService $examService)
    {
        $this->successMessage = '';
        $this->errorMessage = '';

        if (!$this->selectedGrupoId) return;

        $grupo = Grupo::find($this->selectedGrupoId);
        if (!$grupo) return;

        // Validate all grades are correct format
        $gradesToRegister = [];
        foreach ($this->gradesInput as $studentId => $score) {
            if ($score === '' || $score === null) {
                continue;
            }
            if (!is_numeric($score) || $score < 0 || $score > 100) {
                $this->errorMessage = 'Todas las notas deben ser valores numéricos entre 0.00 y 100.00.';
                return;
            }
            $gradesToRegister[$studentId] = (float)$score;
        }

        if (empty($gradesToRegister)) {
            $this->errorMessage = 'No has ingresado ninguna nota válida para guardar.';
            return;
        }

        try {
            DB::transaction(function () use ($grupo, $gradesToRegister, $examService) {
                // Find or create the exam model
                $examen = Examen::where('materia_id', $grupo->materia_id)
                    ->where('gestion_id', $grupo->gestion_id)
                    ->where('nombre', $this->selectedExamenTipo)
                    ->first();

                if (!$examen) {
                    $examen = $examService->createExam(
                        $grupo->materia_id,
                        $grupo->gestion_id,
                        $this->selectedExamenTipo,
                        $this->fechaExamen ?: today()->format('Y-m-d')
                    );
                }

                $examService->registerGrades($examen->id, $gradesToRegister, auth()->id());
            });

            $this->successMessage = '¡Notas guardadas y promedios recalculados con éxito!';
            $this->loadGrades();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al registrar notas: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $user = auth()->user();
        $postulante = null;
        $docente = null;
        $assignedGroups = [];
        $gradesTable = [];

        $availableGroupsByMateria = [];
        $isEnrolled = false;

        if ($this->role === 'Postulante') {
            $postulante = $user->postulante;
            if ($postulante) {
                // Fetch assigned groups
                $assignedGroups = $postulante->grupos()->with(['materia', 'docentes.user', 'horarios'])->get();
                $isEnrolled = $assignedGroups->isNotEmpty();
                
                // Construct grades table
                // For their first career, let's fetch the materias
                $carrera = Carrera::with('materias')->find($postulante->carrera_primera_opcion_id);
                $materias = $carrera ? $carrera->materias : collect();
                
                // If not enrolled and both paid and habilitado, get available groups
                if (!$isEnrolled && $postulante->pago_realizado && $postulante->habilitado) {
                    $activeGestion = Gestion::where('activo', true)->first();
                    foreach ($materias as $materia) {
                        $groups = Grupo::where('materia_id', $materia->id)
                            ->where('gestion_id', $activeGestion?->id)
                            ->with(['docentes.user', 'horarios'])
                            ->get()
                            ->map(function ($grupo) {
                                $grupo->current_postulantes_count = $grupo->postulantes()->count();
                                return $grupo;
                            });
                        $availableGroupsByMateria[$materia->id] = [
                            'materia' => $materia,
                            'groups' => $groups
                        ];
                    }
                }

                $materiaIds = $materias->pluck('id')->toArray();
                $examenes = Examen::whereIn('materia_id', $materiaIds)
                    ->where('gestion_id', $postulante->gestion_id)
                    ->get()
                    ->groupBy('materia_id');

                $examIds = $examenes->flatten()->pluck('id')->toArray();
                $notas = Nota::where('postulante_id', $postulante->id)
                    ->whereIn('examen_id', $examIds)
                    ->get()
                    ->keyBy('examen_id');

                foreach ($materias as $materia) {
                    $materiaExamenes = $examenes->get($materia->id, collect())->keyBy('nombre');

                    $row = [
                        'materia' => $materia->nombre,
                        'sigla' => $materia->sigla,
                        'primer_parcial' => null,
                        'segundo_parcial' => null,
                        'examen_final' => null,
                        'final_grade' => 0.00,
                        'status' => 'Cursando',
                    ];

                    $isComplete = true;

                    foreach (['Primer Parcial' => 'primer_parcial', 'Segundo Parcial' => 'segundo_parcial', 'Examen Final' => 'examen_final'] as $examName => $key) {
                        if (isset($materiaExamenes[$examName])) {
                            $exam = $materiaExamenes[$examName];
                            $notaObj = $notas->get($exam->id);
                            if ($notaObj) {
                                $row[$key] = $notaObj->puntaje;
                                $weight = ($examName === 'Examen Final') ? 0.40 : 0.30;
                                $row['final_grade'] += $notaObj->puntaje * $weight;
                            } else {
                                $isComplete = false;
                            }
                        } else {
                            $isComplete = false;
                        }
                    }

                    if ($isComplete) {
                        $row['status'] = $row['final_grade'] >= 60.00 ? 'Aprobado' : 'Reprobado';
                    }

                    $gradesTable[] = $row;
                }
            }
        } elseif ($this->role === 'Docente') {
            $docente = $user->docente;
            if ($docente) {
                $assignedGroups = $docente->grupos()->with(['materia', 'postulantes'])->get();
            }
        }

        // Get currently selected group for grading view
        $selectedGrupo = null;
        if ($this->selectedGrupoId) {
            $selectedGrupo = Grupo::with(['postulantes', 'materia'])->find($this->selectedGrupoId);
        }

        return view('livewire.dashboard-portal', [
            'postulante' => $postulante,
            'docente' => $docente,
            'assignedGroups' => $assignedGroups,
            'gradesTable' => $gradesTable,
            'selectedGrupo' => $selectedGrupo,
            'availableGroupsByMateria' => $availableGroupsByMateria,
            'isEnrolled' => $isEnrolled,
        ])->layout('layouts.admin');
    }
}
