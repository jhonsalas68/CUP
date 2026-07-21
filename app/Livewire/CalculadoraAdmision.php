<?php

namespace App\Livewire;

use App\Models\Carrera;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use Livewire\Component;

class CalculadoraAdmision extends Component
{
    public $postulanteId = null;
    public $postulante = null;

    public $gestionActiva = null;
    public $carreraPrimera = null;
    public $carreraSegunda = null;

    // Array de materias con sus exámenes y notas (reales o simuladas)
    public $materiasData = [];

    // Target score desired by the student (defaults to 60.00)
    public $targetScore = 60.00;

    // Admin/Docente selector & Filters
    public $carrerasLista = [];
    public $selectedCarreraId = '';
    public $searchPostulante = '';
    public $postulantesLista = [];
    public $selectedPostulanteId = null;

    public function mount($postulanteId = null)
    {
        $this->carrerasLista = Carrera::orderBy('nombre')->get();
        $user = auth()->user();

        if ($user && $user->hasRole('Postulante') && $user->postulante && !$postulanteId) {
            $this->postulante = $user->postulante;
            $this->selectedPostulanteId = $this->postulante->id;
            $this->selectedCarreraId = $this->postulante->carrera_primera_opcion_id ?? '';
        } else {
            if ($postulanteId) {
                $this->postulante = Postulante::find($postulanteId);
                if ($this->postulante) {
                    $this->selectedPostulanteId = $this->postulante->id;
                    $this->selectedCarreraId = $this->postulante->carrera_primera_opcion_id ?? '';
                }
            }

            $this->actualizarListaPostulantes();

            if (!$this->postulante && !empty($this->postulantesLista)) {
                $this->postulante = Postulante::find($this->postulantesLista[0]['id']);
                $this->selectedPostulanteId = $this->postulante?->id;
                $this->selectedCarreraId = $this->postulante?->carrera_primera_opcion_id ?? '';
            }
        }

        $this->cargarDatos();
    }

    public function updatedSelectedCarreraId()
    {
        $this->actualizarListaPostulantes();
        if (!empty($this->postulantesLista)) {
            $this->selectedPostulanteId = $this->postulantesLista[0]['id'];
            $this->postulante = Postulante::find($this->selectedPostulanteId);
        } else {
            $this->selectedPostulanteId = null;
            $this->postulante = null;
        }
        $this->cargarDatos();
    }

    public function updatedSearchPostulante()
    {
        $this->actualizarListaPostulantes();
    }

    public function updatedSelectedPostulanteId($value)
    {
        if ($value) {
            $this->postulante = Postulante::find($value);
            if ($this->postulante) {
                $this->selectedCarreraId = $this->postulante->carrera_primera_opcion_id ?? '';
            }
            $this->cargarDatos();
        }
    }

    public function actualizarListaPostulantes()
    {
        $query = Postulante::with(['carreraPrimeraOpn'])
            ->orderBy('nombres_apellidos');

        if ($this->selectedCarreraId) {
            $query->where('carrera_primera_opcion_id', $this->selectedCarreraId);
        }

        if (trim($this->searchPostulante) !== '') {
            $s = trim($this->searchPostulante);
            $query->where(function ($q) use ($s) {
                $q->where('nombres_apellidos', 'like', "%{$s}%")
                  ->orWhere('ci', 'like', "%{$s}%");
            });
        }

        $this->postulantesLista = $query->limit(200)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nombres_apellidos' => $p->nombres_apellidos,
                'ci' => $p->ci,
                'carrera' => $p->carreraPrimeraOpn?->nombre ?? 'Sin Asignar',
            ];
        })->toArray();
    }

    public function cargarDatos()
    {
        $this->gestionActiva = Gestion::where('activo', true)->first() ?? Gestion::latest()->first();

        if (!$this->postulante || !$this->gestionActiva) {
            $this->materiasData = [];
            return;
        }

        $this->carreraPrimera = $this->postulante->carreraPrimeraOpn;
        $this->carreraSegunda = $this->postulante->carreraSegundaOpn;

        $carreraId = $this->postulante->carrera_primera_opcion_id;
        $materias = Materia::where('carrera_id', $carreraId)->get();

        $this->materiasData = [];

        foreach ($materias as $materia) {
            $examenes = Examen::where('materia_id', $materia->id)
                ->where('gestion_id', $this->gestionActiva->id)
                ->orderBy('fecha')
                ->get();

            $examenesData = [];
            foreach ($examenes as $exam) {
                $notaRealObj = Nota::where('postulante_id', $this->postulante->id)
                    ->where('examen_id', $exam->id)
                    ->first();

                $notaReal = $notaRealObj ? (float)$notaRealObj->puntaje : null;

                $examenesData[] = [
                    'id' => $exam->id,
                    'nombre' => $exam->nombre,
                    'ponderacion' => (float)$exam->ponderacion,
                    'nota_real' => $notaReal,
                    'nota_simulada' => $notaReal !== null ? $notaReal : 0.00,
                    'es_real' => $notaReal !== null,
                ];
            }

            $this->materiasData[] = [
                'materia_id' => $materia->id,
                'materia_nombre' => $materia->nombre,
                'sigla' => $materia->sigla ?? 'MAT',
                'examenes' => $examenesData,
            ];
        }
    }

    /**
     * Helper to compute final projected grade
     */
    public function getPromedioProyectadoProperty()
    {
        if (empty($this->materiasData)) {
            return 0.00;
        }

        $sumMaterias = 0.00;
        $totalMaterias = count($this->materiasData);

        foreach ($this->materiasData as $materia) {
            $notaMateria = 0.00;
            foreach ($materia['examenes'] as $exam) {
                $nota = (float)($exam['nota_simulada'] ?? 0);
                $notaMateria += ($nota * ($exam['ponderacion'] / 100.00));
            }
            $sumMaterias += $notaMateria;
        }

        return round($sumMaterias / max(1, $totalMaterias), 2);
    }

    /**
     * Compute target score needed on missing exams to reach $targetScore
     */
    public function calcularObjetivo()
    {
        $target = (float)$this->targetScore;
        if (empty($this->materiasData)) {
            return;
        }

        $totalMaterias = count($this->materiasData);

        $targetTotalSum = $target * $totalMaterias;
        $fixedSum = 0.00;
        $pendingPonderacionTotal = 0.00;

        foreach ($this->materiasData as $mIndex => $materia) {
            foreach ($materia['examenes'] as $eIndex => $exam) {
                if ($exam['es_real']) {
                    $fixedSum += ($exam['nota_simulada'] * ($exam['ponderacion'] / 100.00));
                } else {
                    $pendingPonderacionTotal += ($exam['ponderacion'] / 100.00);
                }
            }
        }

        if ($pendingPonderacionTotal <= 0) {
            return;
        }

        $neededSum = $targetTotalSum - $fixedSum;
        $neededScorePerPendingWeighted = $neededSum / $pendingPonderacionTotal;
        $neededScoreClean = max(0, min(100, round($neededScorePerPendingWeighted, 2)));

        foreach ($this->materiasData as $mIndex => $materia) {
            foreach ($materia['examenes'] as $eIndex => $exam) {
                if (!$exam['es_real']) {
                    $this->materiasData[$mIndex]['examenes'][$eIndex]['nota_simulada'] = $neededScoreClean;
                }
            }
        }
    }

    public function render()
    {
        $layout = auth()->user() && auth()->user()->hasRole('Postulante') ? 'layouts.app' : 'layouts.admin';

        return view('livewire.calculadora-admision', [
            'promedioProyectado' => $this->promedioProyectado,
        ])->layout($layout, ['title' => 'Calculadora de Admisión - CUP']);
    }
}
