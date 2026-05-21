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

        // 3. Rendimiento por grupo
        $grupos = Grupo::where('gestion_id', $this->selectedGestionId)
            ->with(['materia', 'docentes', 'postulantes'])
            ->get();
            
        $this->gruposRendimiento = [];
        foreach ($grupos as $grupo) {
            $postulantes = $grupo->postulantes;
            $total = $postulantes->count();
            
            $aprobados = 0;
            $promedioSuma = 0;
            $alumnosConNota = 0;
            
            $examenes = Examen::where('materia_id', $grupo->materia_id)
                ->where('gestion_id', $this->selectedGestionId)
                ->get();
                
            foreach ($postulantes as $p) {
                $notaFinal = 0.00;
                $hasNotes = false;
                
                foreach ($examenes as $exam) {
                    $n = Nota::where('postulante_id', $p->id)
                        ->where('examen_id', $exam->id)
                        ->first();
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

        // 4. Estadísticas Históricas
        $gestiones = Gestion::orderBy('fecha_inicio', 'asc')->get();
        $this->historicoLabels = [];
        $this->historicoPostulantes = [];
        $this->historicoAdmitidos = [];
        
        foreach ($gestiones as $g) {
            $this->historicoLabels[] = $g->nombre;
            $this->historicoPostulantes[] = Postulante::where('gestion_id', $g->id)->count();
            $this->historicoAdmitidos[] = Postulante::where('gestion_id', $g->id)
                ->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion'])
                ->count();
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('layouts.admin');
    }
}
