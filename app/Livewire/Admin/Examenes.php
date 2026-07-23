<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use App\Models\PostulanteGrupo;
use App\Services\ExamService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Examenes extends Component
{
    use WithPagination;

    public $search = '';

    public $filterGestion = '';

    public $filterMateria = '';

    // Static dropdown collections
    public $gestiones = [];

    public $materias = [];

    public $carrerasList = [];

    public $showModal = false; // Exam definition modal

    public $isEditing = false;

    public $examenId = null;

    // Form fields for Exam definition
    public $nombre = '';

    public $materia_id = '';

    public $gestion_id = '';

    public $ponderacion = '';

    public $fecha = '';

    // NEW properties for Calificaciones tab
    public $activeTab = 'calificaciones'; // 'calificaciones' or 'configuracion'

    public $showEditNotasModal = false;

    public $showDetailModal = false;

    public $selectedPostulanteId = null;

    public $selectedMateriaId = null;

    public $selectedGrupoName = '';

    public $selectedMateriaNombre = '';

    public $selectedPostulante = null;

    public $nota1erParcial = '';

    public $nota2doParcial = '';

    public $nota3erParcial = '';

    public $postulanteNotas = []; // For detail view

    // Grade filters
    public $filterNotaMin = '';

    public $filterNotaMax = '';

    protected $rules = [
        'nombre' => 'required|in:Primer Parcial,Segundo Parcial,Examen Final',
        'materia_id' => 'required|exists:materias,id',
        'gestion_id' => 'required|exists:gestiones,id',
        'ponderacion' => 'required|numeric|min:1|max:100',
        'fecha' => 'required|date',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre del examen es obligatorio.',
        'nombre.in' => 'El nombre debe ser Primer Parcial, Segundo Parcial o Examen Final.',
        'materia_id.required' => 'La materia es obligatoria.',
        'gestion_id.required' => 'La gestión es obligatoria.',
        'ponderacion.required' => 'La ponderación es obligatoria.',
        'ponderacion.max' => 'La ponderación no puede superar 100.',
        'fecha.required' => 'La fecha es obligatoria.',
    ];

    public function mount()
    {
        if (! auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        $gestionActiva = Gestion::where('activo', true)->first();
        if ($gestionActiva) {
            $this->filterGestion = $gestionActiva->id;
            $this->gestion_id = $gestionActiva->id;
        }

        // Load static dropdowns once
        $this->gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $this->materias = Materia::with('carrera')->orderBy('nombre')->get();
        $this->carrerasList = Carrera::orderBy('nombre')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterGestion()
    {
        $this->resetPage();
    }

    public function updatingFilterMateria()
    {
        $this->resetPage();
    }

    public function updatingActiveTab()
    {
        $this->resetPage();
    }

    public function updatingFilterNotaMin()
    {
        $this->resetPage();
    }

    public function updatingFilterNotaMax()
    {
        $this->resetPage();
    }

    public function updatedNombre($value)
    {
        if ($value === 'Primer Parcial') {
            $this->ponderacion = 30;
        } elseif ($value === 'Segundo Parcial') {
            $this->ponderacion = 30;
        } elseif ($value === 'Examen Final') {
            $this->ponderacion = 40;
        }
    }

    // Exam definition CRUD
    public function openCreate()
    {
        $this->reset(['examenId', 'nombre', 'materia_id', 'ponderacion', 'fecha']);
        $this->resetValidation();
        $gestionActiva = Gestion::where('activo', true)->first();
        $this->gestion_id = $gestionActiva ? $gestionActiva->id : '';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $examen = Examen::findOrFail($id);
        $this->examenId = $examen->id;
        $this->nombre = $examen->nombre;
        $this->materia_id = $examen->materia_id;
        $this->gestion_id = $examen->gestion_id;
        $this->ponderacion = $examen->ponderacion;
        $this->fecha = $examen->fecha ? $examen->fecha->format('Y-m-d') : '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Duplicate check
        $queryDuplicate = Examen::where('materia_id', $this->materia_id)
            ->where('gestion_id', $this->gestion_id)
            ->where('nombre', $this->nombre);

        if ($this->isEditing) {
            $queryDuplicate->where('id', '!=', $this->examenId);
        }

        if ($queryDuplicate->exists()) {
            $this->addError('nombre', "Ya existe un examen con el nombre '{$this->nombre}' para esta materia en la gestión indicada.");

            return;
        }

        // Sum check
        $querySum = Examen::where('materia_id', $this->materia_id)
            ->where('gestion_id', $this->gestion_id);

        if ($this->isEditing) {
            $querySum->where('id', '!=', $this->examenId);
        }

        $sumaActual = $querySum->sum('ponderacion');
        if (($sumaActual + $this->ponderacion) > 100.00) {
            $this->addError('ponderacion', "La ponderación total no puede superar el 100.00%. Actualmente suma {$sumaActual}%, por lo que el máximo permitido para este examen es ".(100.00 - $sumaActual).'%.');

            return;
        }

        DB::transaction(function () {
            if ($this->isEditing) {
                Examen::findOrFail($this->examenId)->update([
                    'nombre' => $this->nombre,
                    'materia_id' => $this->materia_id,
                    'gestion_id' => $this->gestion_id,
                    'ponderacion' => $this->ponderacion,
                    'fecha' => $this->fecha,
                ]);
                session()->flash('message', 'Examen actualizado correctamente.');
            } else {
                Examen::create([
                    'nombre' => $this->nombre,
                    'materia_id' => $this->materia_id,
                    'gestion_id' => $this->gestion_id,
                    'ponderacion' => $this->ponderacion,
                    'fecha' => $this->fecha,
                ]);
                session()->flash('message', 'Examen creado correctamente.');
            }

            // Recalculate scores for all applicants in this gestion
            $postulantes = Postulante::where('gestion_id', $this->gestion_id)->get();
            $examService = new ExamService;
            foreach ($postulantes as $postulante) {
                $examService->recalculatePostulanteScore($postulante->id, $this->gestion_id);
            }
        });

        $this->showModal = false;
        $this->reset(['examenId', 'nombre', 'materia_id', 'ponderacion', 'fecha']);
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $examen = Examen::findOrFail($id);
            $gestionId = $examen->gestion_id;
            $examen->delete();

            // Recalculate scores for all applicants in this gestion
            $postulantes = Postulante::where('gestion_id', $gestionId)->get();
            $examService = new ExamService;
            foreach ($postulantes as $postulante) {
                $examService->recalculatePostulanteScore($postulante->id, $gestionId);
            }
        });

        session()->flash('message', 'Examen y notas recalculated / eliminado correctamente.');
    }

    // Individual Grades Operations
    public function openDetail($postulanteId)
    {
        $this->selectedPostulante = Postulante::with([
            'carreraPrimeraOpn',
            'carreraSegundaOpn',
            'gestion',
            'notas.examen.materia',
            'grupos.docentes',
        ])->findOrFail($postulanteId);

        $carreraId = $this->selectedPostulante->carrera_primera_opcion_id;
        $materias = Materia::where('carrera_id', $carreraId)->get();

        $this->postulanteNotas = [];

        foreach ($materias as $materia) {
            $examenes = Examen::where('materia_id', $materia->id)
                ->where('gestion_id', $this->selectedPostulante->gestion_id)
                ->get();

            $notasMateria = [];
            $notaMateriaAcumulada = 0.00;

            foreach (['Primer Parcial', 'Segundo Parcial', 'Examen Final'] as $tipo) {
                $exam = $examenes->where('nombre', $tipo)->first();
                $puntaje = null;
                if ($exam) {
                    $nota = $this->selectedPostulante->notas->where('examen_id', $exam->id)->first();
                    $puntaje = $nota ? $nota->puntaje : null;
                    if ($puntaje !== null) {
                        $notaMateriaAcumulada += ($puntaje * ($exam->ponderacion / 100.00));
                    }
                }
                $notasMateria[$tipo] = $puntaje;
            }

            $grupo = $this->selectedPostulante->grupos->where('materia_id', $materia->id)->first();
            $grupoNombre = $grupo ? $grupo->nombre : 'Sin grupo';
            $docenteNombre = ($grupo && $grupo->docentes->first()) ? $grupo->docentes->first()->nombre : 'No asignado';

            $this->postulanteNotas[] = [
                'materia_nombre' => $materia->nombre,
                'grupo_nombre' => $grupoNombre,
                'docente_nombre' => $docenteNombre,
                'primer_parcial' => $notasMateria['Primer Parcial'],
                'segundo_parcial' => $notasMateria['Segundo Parcial'],
                'examen_final' => $notasMateria['Examen Final'],
                'total_materia' => round($notaMateriaAcumulada, 2),
            ];
        }

        $this->showDetailModal = true;
    }

    public function openEditNotas($postulanteId, $materiaId, $grupoName, $materiaNombre)
    {
        $this->selectedPostulanteId = $postulanteId;
        $this->selectedMateriaId = $materiaId;
        $this->selectedGrupoName = $grupoName;
        $this->selectedMateriaNombre = $materiaNombre;

        $postulante = Postulante::findOrFail($postulanteId);
        $this->selectedPostulante = $postulante;

        $examenes = Examen::where('materia_id', $materiaId)
            ->where('gestion_id', $postulante->gestion_id)
            ->get();

        $exam1 = $examenes->where('nombre', 'Primer Parcial')->first();
        $nota1 = $exam1 ? Nota::where('postulante_id', $postulanteId)->where('examen_id', $exam1->id)->first() : null;
        $this->nota1erParcial = $nota1 ? $nota1->puntaje : '';

        $exam2 = $examenes->where('nombre', 'Segundo Parcial')->first();
        $nota2 = $exam2 ? Nota::where('postulante_id', $postulanteId)->where('examen_id', $exam2->id)->first() : null;
        $this->nota2doParcial = $nota2 ? $nota2->puntaje : '';

        $exam3 = $examenes->where('nombre', 'Examen Final')->first();
        $nota3 = $exam3 ? Nota::where('postulante_id', $postulanteId)->where('examen_id', $exam3->id)->first() : null;
        $this->nota3erParcial = $nota3 ? $nota3->puntaje : '';

        $this->resetValidation();
        $this->showEditNotasModal = true;
    }

    public function saveNotas()
    {
        $this->validate([
            'nota1erParcial' => 'nullable|numeric|min:0|max:100',
            'nota2doParcial' => 'nullable|numeric|min:0|max:100',
            'nota3erParcial' => 'nullable|numeric|min:0|max:100',
        ], [
            'nota1erParcial.numeric' => 'La nota debe ser un número.',
            'nota1erParcial.min' => 'La nota no puede ser menor a 0.',
            'nota1erParcial.max' => 'La nota no puede superar 100.',
            'nota2doParcial.numeric' => 'La nota debe ser un número.',
            'nota2doParcial.min' => 'La nota no puede ser menor a 0.',
            'nota2doParcial.max' => 'La nota no puede superar 100.',
            'nota3erParcial.numeric' => 'La nota debe ser un número.',
            'nota3erParcial.min' => 'La nota no puede ser menor a 0.',
            'nota3erParcial.max' => 'La nota no puede superar 100.',
        ]);

        $postulante = Postulante::findOrFail($this->selectedPostulanteId);
        $gestionId = $postulante->gestion_id;

        DB::transaction(function () use ($postulante, $gestionId) {
            $examTypes = [
                'Primer Parcial' => $this->nota1erParcial,
                'Segundo Parcial' => $this->nota2doParcial,
                'Examen Final' => $this->nota3erParcial,
            ];

            foreach ($examTypes as $tipo => $val) {
                $exam = Examen::where('materia_id', $this->selectedMateriaId)
                    ->where('gestion_id', $gestionId)
                    ->where('nombre', $tipo)
                    ->first();

                if (! $exam) {
                    $ponderacion = $tipo === 'Examen Final' ? 40.00 : 30.00;
                    $exam = Examen::create([
                        'nombre' => $tipo,
                        'materia_id' => $this->selectedMateriaId,
                        'gestion_id' => $gestionId,
                        'ponderacion' => $ponderacion,
                        'fecha' => now()->format('Y-m-d'),
                    ]);
                }

                if ($val !== '' && $val !== null) {
                    Nota::updateOrCreate(
                        [
                            'postulante_id' => $postulante->id,
                            'examen_id' => $exam->id,
                        ],
                        [
                            'puntaje' => floatval($val),
                            'registrado_por' => auth()->id(),
                        ]
                    );
                } else {
                    Nota::where('postulante_id', $postulante->id)
                        ->where('examen_id', $exam->id)
                        ->delete();
                }
            }

            $examService = new ExamService;
            $examService->recalculatePostulanteScore($postulante->id, $gestionId);
        });

        session()->flash('message', 'Calificaciones actualizadas correctamente.');
        $this->showEditNotasModal = false;
        $this->reset(['selectedPostulanteId', 'selectedMateriaId', 'selectedGrupoName', 'selectedMateriaNombre', 'nota1erParcial', 'nota2doParcial', 'nota3erParcial', 'selectedPostulante']);
    }

    public function deleteNotas($postulanteId, $materiaId)
    {
        $postulante = Postulante::findOrFail($postulanteId);
        $gestionId = $postulante->gestion_id;

        DB::transaction(function () use ($postulanteId, $materiaId, $gestionId) {
            $examIds = Examen::where('materia_id', $materiaId)
                ->where('gestion_id', $gestionId)
                ->pluck('id');

            Nota::where('postulante_id', $postulanteId)
                ->whereIn('examen_id', $examIds)
                ->delete();

            $examService = new ExamService;
            $examService->recalculatePostulanteScore($postulanteId, $gestionId);
        });

        session()->flash('message', 'Calificaciones restablecidas correctamente.');
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filterMateria', 'filterGestion', 'filterNotaMin', 'filterNotaMax']);
        $this->resetPage();
    }

    public function processVoiceCommand($transcript)
    {
        $transcript = mb_strtolower($transcript, 'UTF-8');
        $transcript = $this->normalizeNumbers($transcript);

        if (str_contains($transcript, 'limpiar') || str_contains($transcript, 'restablecer') || str_contains($transcript, 'todos') || str_contains($transcript, 'reiniciar') || str_contains($transcript, 'quitar')) {
            $this->reset(['search', 'filterMateria', 'filterGestion', 'filterNotaMin', 'filterNotaMax']);
            session()->flash('voice_feedback', 'Filtros restablecidos.');
            $this->resetPage();

            return;
        }

        $feedback = [];

        // Parsear Materia
        if (str_contains($transcript, 'matemática') || str_contains($transcript, 'matematica')) {
            $m = Materia::where('nombre', 'like', '%Matemáticas%')->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: '.$m->nombre;
            }
        } elseif (str_contains($transcript, 'física') || str_contains($transcript, 'fisica')) {
            $m = Materia::where('nombre', 'like', '%Física%')->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: '.$m->nombre;
            }
        } elseif (str_contains($transcript, 'inglés') || str_contains($transcript, 'ingles')) {
            $m = Materia::where('nombre', 'like', '%Inglés%')->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: '.$m->nombre;
            }
        } elseif (str_contains($transcript, 'computación') || str_contains($transcript, 'computacion')) {
            $m = Materia::where('nombre', 'like', '%Computación%')->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: '.$m->nombre;
            }
        }

        // Parsear Gestión
        if (preg_match('/gestión\s+([a-z0-9\-]+)/', $transcript, $matches) || preg_match('/gestion\s+([a-z0-9\-]+)/', $transcript, $matches)) {
            $gestName = strtoupper($matches[1]);
            $g = Gestion::where('nombre', 'like', '%'.$gestName.'%')->first();
            if ($g) {
                $this->filterGestion = $g->id;
                $feedback[] = 'Gestión: '.$g->nombre;
            }
        } elseif (preg_match('/(2025|2026)/', $transcript, $matches)) {
            $year = $matches[1];
            $g = Gestion::where('nombre', 'like', '%'.$year.'%')->first();
            if ($g) {
                $this->filterGestion = $g->id;
                $feedback[] = 'Gestión: '.$g->nombre;
            }
        }

        // Parsear Notas (Nota Ponderada)
        if (preg_match('/nota\s+(?:ponderada\s+)?(?:mayor|superior|más\s+de|mas\s+de)\s+(?:a\s+|de\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $feedback[] = 'Nota >= '.$matches[1];
            $this->activeTab = 'calificaciones';
        } elseif (preg_match('/nota\s+(?:ponderada\s+)?(?:menor|inferior|menos\s+de)\s+(?:a\s+|de\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMax = $matches[1];
            $feedback[] = 'Nota <= '.$matches[1];
            $this->activeTab = 'calificaciones';
        } elseif (preg_match('/nota\s+(?:ponderada\s+)?entre\s+(\d+)\s+y\s+(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $this->filterNotaMax = $matches[2];
            $feedback[] = "Nota entre {$matches[1]} y {$matches[2]}";
            $this->activeTab = 'calificaciones';
        } elseif (preg_match('/nota\s+(?:ponderada\s+)?(?:de\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $feedback[] = 'Nota >= '.$matches[1];
            $this->activeTab = 'calificaciones';
        }

        // Map common exam names to their title-case versions
        if (str_contains($transcript, 'primer parcial')) {
            $this->search = 'Primer Parcial';
            $feedback[] = 'Búsqueda: "Primer Parcial"';
        } elseif (str_contains($transcript, 'segundo parcial')) {
            $this->search = 'Segundo Parcial';
            $feedback[] = 'Búsqueda: "Segundo Parcial"';
        } elseif (str_contains($transcript, 'examen final')) {
            $this->search = 'Examen Final';
            $feedback[] = 'Búsqueda: "Examen Final"';
        }

        // Búsqueda general
        if (empty($feedback)) {
            if (preg_match('/(?:buscar|busca|nombre|examen)\s+([a-záéíóúñ0-9\s\-]+)/', $transcript, $matches)) {
                $this->search = trim($matches[1]);
                $feedback[] = 'Búsqueda: "'.$this->search.'"';
            }
        }

        if (empty($feedback)) {
            $this->search = trim($transcript);
            $feedback[] = 'Búsqueda: "'.$this->search.'"';
            session()->flash('voice_feedback', 'Búsqueda de texto: "'.$this->search.'"');
        } else {
            session()->flash('voice_feedback', 'Filtros aplicados: '.implode(', ', $feedback));
        }

        $this->resetPage();
    }

    private function normalizeNumbers($text)
    {
        $words = [
            'cero' => 0, 'uno' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4, 'cinco' => 5,
            'seis' => 6, 'siete' => 7, 'ocho' => 8, 'nueve' => 9, 'diez' => 10,
            'once' => 11, 'doce' => 12, 'trece' => 13, 'catorce' => 14, 'quince' => 15,
            'dieciséis' => 16, 'dieciseis' => 16, 'diecisiete' => 17, 'dieciocho' => 18, 'diecinueve' => 19,
            'veinte' => 20, 'veintiuno' => 21, 'veintidós' => 22, 'veintidos' => 22, 'veintitres' => 23, 'veintitrés' => 23,
            'veinticuatro' => 24, 'veinticinco' => 25, 'veintiséis' => 26, 'veintiseis' => 26, 'veintisiete' => 27,
            'veintiocho' => 28, 'veintinueve' => 29, 'treinta' => 30, 'cuarenta' => 40, 'cincuenta' => 50,
            'sesenta' => 60, 'setenta' => 70, 'ochenta' => 80, 'noventa' => 90, 'cien' => 100,
        ];

        $tens = [
            'treinta' => 30,
            'cuarenta' => 40,
            'cincuenta' => 50,
            'sesenta' => 60,
            'setenta' => 70,
            'ochenta' => 80,
            'noventa' => 90,
        ];
        $units = [
            'uno' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4, 'cinco' => 5,
            'seis' => 6, 'siete' => 7, 'ocho' => 8, 'nueve' => 9,
        ];

        foreach ($tens as $tenWord => $tenVal) {
            foreach ($units as $unitWord => $unitVal) {
                $text = preg_replace('/\b'.$tenWord.'\s+y\s+'.$unitWord.'\b/u', $tenVal + $unitVal, $text);
            }
        }

        foreach ($words as $word => $num) {
            $text = preg_replace('/\b'.$word.'\b/u', $num, $text);
        }

        return $text;
    }

    public function render()
    {
        $examenes = null;
        $calificaciones = null;

        if ($this->activeTab === 'calificaciones') {
            $calificaciones = PostulanteGrupo::query()
                ->join('postulantes', 'postulante_grupo.postulante_id', '=', 'postulantes.id')
                ->join('grupos', 'postulante_grupo.grupo_id', '=', 'grupos.id')
                ->join('materias', 'grupos.materia_id', '=', 'materias.id')
                ->join('gestiones', 'grupos.gestion_id', '=', 'gestiones.id')
                ->select('postulante_grupo.*')
                ->with([
                    'postulante.notas.examen',
                    'grupo.materia.carrera',
                    'grupo.docentes',
                    'grupo.gestion',
                ])
                ->where(function ($q) {
                    if ($this->search) {
                        $q->where('postulantes.nombres_apellidos', 'like', '%'.$this->search.'%')
                            ->orWhere('postulantes.ci', 'like', '%'.$this->search.'%')
                            ->orWhere('materias.nombre', 'like', '%'.$this->search.'%')
                            ->orWhere('grupos.nombre', 'like', '%'.$this->search.'%');
                    }
                })
                ->when($this->filterGestion, fn ($q) => $q->where('grupos.gestion_id', $this->filterGestion))
                ->when($this->filterMateria, fn ($q) => $q->where('grupos.materia_id', $this->filterMateria))
                ->when($this->filterNotaMin !== '', function ($q) {
                    $q->whereRaw('CAST((
                        SELECT COALESCE(SUM(n.puntaje * (e.ponderacion / 100.00)), 0.00)
                        FROM notas n
                        JOIN examenes e ON n.examen_id = e.id
                        WHERE n.postulante_id = postulante_grupo.postulante_id
                          AND e.materia_id = materias.id
                          AND e.gestion_id = grupos.gestion_id
                    ) AS NUMERIC) >= ?', [(float) $this->filterNotaMin]);
                })
                ->when($this->filterNotaMax !== '', function ($q) {
                    $q->whereRaw('CAST((
                        SELECT COALESCE(SUM(n.puntaje * (e.ponderacion / 100.00)), 0.00)
                        FROM notas n
                        JOIN examenes e ON n.examen_id = e.id
                        WHERE n.postulante_id = postulante_grupo.postulante_id
                          AND e.materia_id = materias.id
                          AND e.gestion_id = grupos.gestion_id
                    ) AS NUMERIC) <= ?', [(float) $this->filterNotaMax]);
                })
                ->orderBy('postulantes.nombres_apellidos', 'asc')
                ->paginate(50);
        } else {
            $examenes = Examen::query()
                ->with(['materia.carrera', 'gestion', 'materia.grupos.docentes', 'materia.grupos.postulantes'])
                ->where(function ($q) {
                    if ($this->search) {
                        $q->where('nombre', 'like', '%'.$this->search.'%');
                    }
                })
                ->when($this->filterGestion, fn ($q) => $q->where('gestion_id', $this->filterGestion))
                ->when($this->filterMateria, fn ($q) => $q->where('materia_id', $this->filterMateria))
                ->orderBy('materia_id', 'asc')
                ->orderBy('fecha', 'asc')
                ->paginate(50);
        }

        return view('livewire.admin.examenes', [
            'examenes' => $examenes,
            'calificaciones' => $calificaciones,
            'gestiones' => $this->gestiones,
            'materias' => $this->materias,
            'carrerasList' => $this->carrerasList,
        ])->layout('layouts.admin');
    }
}
