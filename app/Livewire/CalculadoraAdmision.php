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

    // Admin/Docente selector
    public $postulantesLista = [];
    public $selectedPostulanteId = null;

    public function mount($postulanteId = null)
    {
        $user = auth()->user();

        if ($user && $user->hasRole('Postulante')) {
            $this->postulante = $user->postulante;
        } else {
            // If Admin or Docente, load list of postulantes for selection
            $this->postulantesLista = Postulante::with(['user', 'carreraPrimeraOpn'])
                ->orderBy('nombres_apellidos')
                ->limit(50)
                ->get();

            if ($postulanteId) {
                $this->postulante = Postulante::find($postulanteId);
                $this->selectedPostulanteId = $postulanteId;
            } elseif ($this->postulantesLista->isNotEmpty()) {
                $this->postulante = $this->postulantesLista->first();
                $this->selectedPostulanteId = $this->postulante->id;
            }
        }

        $this->cargarDatos();
    }

    public function updatedSelectedPostulanteId($value)
    {
        if ($value) {
            $this->postulante = Postulante::find($value);
            $this->cargarDatos();
        }
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
