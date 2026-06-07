<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Cupo;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Grupo;
use App\Models\Nota;
use App\Models\Postulante;
use Livewire\Component;

class Dashboard extends Component
{
    public $selectedGestionId;
    public $gestiones = [];
    
    // stats
    public $totalPostulantes = 0;
    public $totalAdmitidos = 0;
    public $totalReprobados = 0;
    public $totalCuposDisponibles = 0;
    public $totalCuposOcupados = 0;
    
    // charts data
    public $carrerasLabels = [];
    public $carrerasValues = [];
    
    public $historicoLabels = [];
    public $historicoPostulantes = [];
    public $historicoAdmitidos = [];
    
    public $gruposRendimiento = [];

    // admission execution properties
    public $showAdmissionModal = false;
    public $isProcessing = false;
    public $admissionError = null;
    public $admissionStats = null;

    // group detail properties
    public $showGroupDetailsModal = false;
    public $selectedGroupInfo = null;
    public $groupAlumnos = [];

    public function mount()
    {
        if (!auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        $this->gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $active = $this->gestiones->where('activo', true)->first() ?? $this->gestiones->first();
        $this->selectedGestionId = $active ? $active->id : null;
        
        $this->loadStats();
    }

    public function updatedSelectedGestionId()
    {
        $this->loadStats();
        // Dispatch event for charts reload
        $this->dispatch('stats-updated');
    }

    public function loadStats()
    {
        if (!$this->selectedGestionId) {
            return;
        }

        // 1. KPIs
        $this->totalPostulantes = Postulante::where('gestion_id', $this->selectedGestionId)->count();
        $this->totalAdmitidos = Postulante::where('gestion_id', $this->selectedGestionId)
            ->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion'])
            ->count();
        $this->totalReprobados = Postulante::where('gestion_id', $this->selectedGestionId)
            ->where('estado_admision', 'reprobado')
            ->count();
            
        $this->totalCuposDisponibles = Cupo::where('gestion_id', $this->selectedGestionId)->sum('cantidad_primera_opcion')
            + Cupo::where('gestion_id', $this->selectedGestionId)->sum('cantidad_segunda_opcion');
            
        $this->totalCuposOcupados = $this->totalAdmitidos;

        // 2. Carreras más demandadas (evita N+1 con un solo query)
        $demanda = Postulante::where('gestion_id', $this->selectedGestionId)
            ->select('carrera_primera_opcion_id', \DB::raw('count(*) as total'))
            ->groupBy('carrera_primera_opcion_id')
            ->orderBy('total', 'desc')
            ->get();

        $carrerasMap = Carrera::whereIn('id', $demanda->pluck('carrera_primera_opcion_id'))
            ->pluck('sigla', 'id');

        $this->carrerasLabels = [];
        $this->carrerasValues = [];
        foreach ($demanda as $item) {
            if (isset($carrerasMap[$item->carrera_primera_opcion_id])) {
                $this->carrerasLabels[] = $carrerasMap[$item->carrera_primera_opcion_id];
                $this->carrerasValues[] = $item->total;
            }
        }

        // 3. Rendimiento por grupo (Optimizado para evitar consultas N+1 en bucle)
        $grupos = Grupo::where('gestion_id', $this->selectedGestionId)
            ->with(['materia', 'docentes.user', 'postulantes'])
            ->get();

        // Obtener todos los exámenes de la gestión en una sola consulta
        $materiaIds = $grupos->pluck('materia_id')->unique();
        $examenes = Examen::whereIn('materia_id', $materiaIds)
            ->where('gestion_id', $this->selectedGestionId)
            ->get()
            ->groupBy('materia_id');

        // Obtener todas las notas de los exámenes de la gestión en una sola consulta
        $examIds = $examenes->flatten()->pluck('id')->unique();
        $notas = Nota::whereIn('examen_id', $examIds)
            ->get()
            ->groupBy('postulante_id');
            
        $this->gruposRendimiento = [];
        foreach ($grupos as $grupo) {
            $postulantes = $grupo->postulantes;
            $total = $postulantes->count();
            
            $aprobados = 0;
            $promedioSuma = 0;
            $alumnosConNota = 0;
            
            $grupoExamenes = $examenes->get($grupo->materia_id, collect());
                
            foreach ($postulantes as $p) {
                $notaFinal = 0.00;
                $hasNotes = false;
                
                // Obtener las notas del postulante en memoria
                $postulanteNotas = $notas->get($p->id, collect())->keyBy('examen_id');
                
                foreach ($grupoExamenes as $exam) {
                    $n = $postulanteNotas->get($exam->id);
                    if ($n) {
                        $notaFinal += $n->puntaje * ($exam->ponderacion / 100);
                        $hasNotes = true;
                    }
                }
                
                if ($hasNotes) {
                    $promedioSuma += $notaFinal;
                    $alumnosConNota++;
                    if ($notaFinal >= 60.00) {
                        $aprobados++;
                    }
                }
            }
            
            $promedioGrupo = $alumnosConNota > 0 ? round($promedioSuma / $alumnosConNota, 2) : 0.00;
            $porcentajeAprobacion = $total > 0 ? round(($aprobados / $total) * 100, 2) : 0.00;
            
            $docenteNombre = $grupo->docentes->first() ? $grupo->docentes->first()->user->name : 'No asignado';
            
            $this->gruposRendimiento[] = [
                'id' => $grupo->id,
                'nombre' => $grupo->nombre,
                'materia' => $grupo->materia->nombre,
                'docente' => $docenteNombre,
                'total_alumnos' => $total,
                'promedio' => $promedioGrupo,
                'tasa_aprobacion' => $porcentajeAprobacion,
            ];
        }

        // 4. Estadísticas Históricas (Optimizado para evitar consultas N+1 en bucle)
        $gestiones = Gestion::orderBy('fecha_inicio', 'asc')->get();
        
        $postulantesCounts = Postulante::select('gestion_id', \DB::raw('count(*) as total'))
            ->groupBy('gestion_id')
            ->pluck('total', 'gestion_id');

        $admitidosCounts = Postulante::select('gestion_id', \DB::raw('count(*) as total'))
            ->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion'])
            ->groupBy('gestion_id')
            ->pluck('total', 'gestion_id');

        $this->historicoLabels = [];
        $this->historicoPostulantes = [];
        $this->historicoAdmitidos = [];
        
        foreach ($gestiones as $g) {
            $this->historicoLabels[] = $g->nombre;
            $this->historicoPostulantes[] = $postulantesCounts->get($g->id, 0);
            $this->historicoAdmitidos[] = $admitidosCounts->get($g->id, 0);
        }
    }

    public function openAdmissionProcess()
    {
        $this->reset(['admissionError', 'admissionStats', 'isProcessing']);
        $this->showAdmissionModal = true;
    }

    public function runAdmissionProcess(\App\Services\AdmissionSelectionService $service)
    {
        $this->isProcessing = true;
        $this->admissionError = null;
        $this->admissionStats = null;

        try {
            $res = $service->processAdmissions($this->selectedGestionId);
            
            if ($res['success']) {
                // Load fresh stats for summary
                $this->admissionStats = $service->getStats($this->selectedGestionId);
                
                // Reload parent dashboard numbers
                $this->loadStats();
                
                // Dispatch event to refresh charts
                $this->dispatch('stats-updated');
            } else {
                $this->admissionError = "El proceso no pudo completarse correctamente.";
            }
        } catch (\App\Exceptions\AdmissionSelectionException $e) {
            $this->admissionError = $e->getMessage();
        } catch (\Exception $e) {
            $this->admissionError = "Ocurrió un error inesperado al procesar las admisiones: " . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function showGroupDetails($groupId)
    {
        $this->reset(['selectedGroupInfo', 'groupAlumnos']);
        
        $grupo = \App\Models\Grupo::with(['materia', 'docentes.user', 'postulantes.user'])
            ->findOrFail($groupId);
            
        $docenteNombre = $grupo->docentes->first() ? $grupo->docentes->first()->user->name : 'No asignado';
        
        $this->selectedGroupInfo = [
            'id' => $grupo->id,
            'nombre' => $grupo->nombre,
            'materia' => $grupo->materia->nombre,
            'docente' => $docenteNombre,
        ];

        // Optimized 3-query calculation of student grades in that group's subject
        $exams = \App\Models\Examen::where('materia_id', $grupo->materia_id)
            ->where('gestion_id', $this->selectedGestionId)
            ->get();

        $notas = \App\Models\Nota::whereIn('examen_id', $exams->pluck('id'))
            ->whereIn('postulante_id', $grupo->postulantes->pluck('id'))
            ->get()
            ->groupBy('postulante_id');

        $this->groupAlumnos = [];
        foreach ($grupo->postulantes as $p) {
             $notaFinal = 0.00;
             $postulanteNotas = $notas->get($p->id, collect())->keyBy('examen_id');
             foreach ($exams as $exam) {
                 $n = $postulanteNotas->get($exam->id);
                 if ($n) {
                     $notaFinal += $n->puntaje * ($exam->ponderacion / 100);
                 }
             }
             $this->groupAlumnos[] = [
                 'nombre' => $p->user->name,
                 'email' => $p->user->email,
                 'ci' => $p->ci,
                 'nota_materia' => round($notaFinal, 2),
             ];
        }

        $this->showGroupDetailsModal = true;
    }

    public function closeGroupDetails()
    {
        $this->showGroupDetailsModal = false;
        $this->reset(['selectedGroupInfo', 'groupAlumnos']);
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('layouts.admin');
    }
}
