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
    public $totalGrupos = 0;
    
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

    // custom export properties
    public $exportTabla = 'postulantes';
    public $exportGestionId = '';
    public $exportCarreraId = '';
    public $exportFormato = 'excel';
    public $carrerasList = [];

    // detailed admitted candidates properties
    public $selectedDetailCarreraId;
    public $admitidosDetalle = [];

    public function mount()
    {
        if (!auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        $this->gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $active = $this->gestiones->where('activo', true)->first() ?? $this->gestiones->first();
        $this->selectedGestionId = $active ? $active->id : null;
        
        $this->carrerasList = Carrera::orderBy('nombre')->get();
        $this->exportGestionId = $this->selectedGestionId;

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
        $this->totalGrupos = Grupo::where('gestion_id', $this->selectedGestionId)->count();

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

        // 3. Rendimiento por grupo (Altamente optimizado con agregación SQL)
        $statsRaw = \DB::select("
            SELECT 
                g.id as grupo_id,
                g.nombre as grupo_nombre,
                m.nombre as materia_nombre,
                COUNT(pg.postulante_id) as total_alumnos,
                COALESCE(AVG(student_grades.nota_materia), 0) as promedio_grupo,
                COALESCE(SUM(CASE WHEN student_grades.nota_materia >= 60.00 THEN 1 ELSE 0 END), 0) as aprobados
            FROM grupos g
            JOIN materias m ON g.materia_id = m.id
            LEFT JOIN postulante_grupo pg ON g.id = pg.grupo_id
            LEFT JOIN (
                SELECT 
                    pg2.grupo_id,
                    pg2.postulante_id,
                    COALESCE(SUM(n.puntaje * e.ponderacion / 100), 0) as nota_materia
                FROM postulante_grupo pg2
                JOIN grupos g2 ON pg2.grupo_id = g2.id
                JOIN examenes e ON g2.materia_id = e.materia_id AND g2.gestion_id = e.gestion_id
                LEFT JOIN notas n ON pg2.postulante_id = n.postulante_id AND e.id = n.examen_id
                WHERE g2.gestion_id = :gestion_id_1
                GROUP BY pg2.grupo_id, pg2.postulante_id
            ) as student_grades ON g.id = student_grades.grupo_id AND pg.postulante_id = student_grades.postulante_id
            WHERE g.gestion_id = :gestion_id_2
            GROUP BY g.id, g.nombre, m.nombre
            ORDER BY g.nombre
        ", [
            'gestion_id_1' => $this->selectedGestionId,
            'gestion_id_2' => $this->selectedGestionId,
        ]);

        $grupoDocentes = \DB::table('asignaciones_docente')
            ->join('docentes', 'asignaciones_docente.docente_id', '=', 'docentes.id')
            ->join('grupos', 'asignaciones_docente.grupo_id', '=', 'grupos.id')
            ->where('grupos.gestion_id', $this->selectedGestionId)
            ->select('asignaciones_docente.grupo_id', 'docentes.nombre')
            ->get()
            ->groupBy('grupo_id');

        $this->gruposRendimiento = [];
        foreach ($statsRaw as $row) {
            $docenteNombre = 'No asignado';
            if (isset($grupoDocentes[$row->grupo_id])) {
                $docenteNombre = $grupoDocentes[$row->grupo_id]->pluck('nombre')->first() ?? 'No asignado';
            }

            $total = (int) $row->total_alumnos;
            $aprobados = (int) $row->aprobados;
            
            $promedioGrupo = round((float) $row->promedio_grupo, 2);
            $porcentajeAprobacion = $total > 0 ? round(($aprobados / $total) * 100, 2) : 0.00;

            $this->gruposRendimiento[] = [
                'id' => $row->grupo_id,
                'nombre' => $row->grupo_nombre,
                'materia' => $row->materia_nombre,
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

        if (!$this->selectedDetailCarreraId && count($this->carrerasList) > 0) {
            $this->selectedDetailCarreraId = $this->carrerasList->first()->id;
        }
        $this->loadAdmitidosDetalle();
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
            
        $docenteNombre = $grupo->docentes->first() ? ($grupo->docentes->first()->nombre ?? $grupo->docentes->first()->user->name) : 'No asignado';
        
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
                 'nombre' => $p->nombres_apellidos ?? $p->user->name,
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

    public function loadAdmitidosDetalle()
    {
        if (!$this->selectedGestionId || !$this->selectedDetailCarreraId) {
            $this->admitidosDetalle = [];
            return;
        }

        $this->admitidosDetalle = Postulante::where('gestion_id', $this->selectedGestionId)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('carrera_primera_opcion_id', $this->selectedDetailCarreraId)
                      ->where('estado_admision', 'admitido_primera_opcion');
                })->orWhere(function ($q) {
                    $q->where('carrera_segunda_opcion_id', $this->selectedDetailCarreraId)
                      ->where('estado_admision', 'admitido_segunda_opcion');
                });
            })
            ->orderByDesc('nota_final')
            ->orderBy('id')
            ->get()
            ->map(function ($p, $index) {
                return [
                    'ranking' => $index + 1,
                    'nombre' => $p->nombres_apellidos,
                    'ci' => $p->ci,
                    'nota_final' => $p->nota_final,
                    'opcion' => $p->estado_admision === 'admitido_primera_opcion' ? 1 : 2,
                ];
            })
            ->toArray();
    }

    public function updatedSelectedDetailCarreraId()
    {
        $this->loadAdmitidosDetalle();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('layouts.admin');
    }
}
