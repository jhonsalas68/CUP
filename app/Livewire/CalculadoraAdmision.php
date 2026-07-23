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

        if ($user && $user->hasRole('Postulante') && $user->postulante && ! $postulanteId) {
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

            if (! $this->postulante && ! empty($this->postulantesLista)) {
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
        if (! empty($this->postulantesLista)) {
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

        if (! $this->postulante || ! $this->gestionActiva) {
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

                $notaReal = $notaRealObj ? (float) $notaRealObj->puntaje : null;

                $examenesData[] = [
                    'id' => $exam->id,
                    'nombre' => $exam->nombre,
                    'ponderacion' => (float) $exam->ponderacion,
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
                $nota = (float) ($exam['nota_simulada'] ?? 0);
                $notaMateria += ($nota * ($exam['ponderacion'] / 100.00));
            }
            $sumMaterias += $notaMateria;
        }

        return round($sumMaterias / max(1, $totalMaterias), 2);
    }

    /**
     * Restablecer todas las notas simuladas a las notas oficiales registradas
     */
    public function restablecerNotasOficiales()
    {
        foreach ($this->materiasData as $mIndex => $materia) {
            foreach ($materia['examenes'] as $eIndex => $exam) {
                $this->materiasData[$mIndex]['examenes'][$eIndex]['nota_simulada'] = $exam['nota_real'] !== null ? $exam['nota_real'] : 0.00;
            }
        }
    }

    /**
     * Compute target score needed on pending exams to reach $targetScore
     */
    public function calcularObjetivo()
    {
        $target = (float) $this->targetScore;
        if (empty($this->materiasData)) {
            return;
        }

        $totalMaterias = count($this->materiasData);
        $targetTotalSum = $target * $totalMaterias;

        $fixedSum = 0.00;
        $pendingPonderacionTotal = 0.00;

        foreach ($this->materiasData as $mIndex => $materia) {
            foreach ($materia['examenes'] as $eIndex => $exam) {
                if (! empty($exam['es_real'])) {
                    $fixedSum += ($exam['nota_simulada'] * ($exam['ponderacion'] / 100.00));
                } else {
                    $pendingPonderacionTotal += ($exam['ponderacion'] / 100.00);
                }
            }
        }

        // Si todos los exámenes ya son oficiales o no hay pendientes, calcular uniformemente
        if ($pendingPonderacionTotal <= 0) {
            $scoreNeeded = max(0, min(100, round($targetTotalSum / max(1, $totalMaterias), 2)));
            foreach ($this->materiasData as $mIndex => $materia) {
                foreach ($materia['examenes'] as $eIndex => $exam) {
                    $this->materiasData[$mIndex]['examenes'][$eIndex]['nota_simulada'] = $scoreNeeded;
                }
            }

            return;
        }

        $neededSum = $targetTotalSum - $fixedSum;
        $neededScorePerPendingWeighted = $neededSum / $pendingPonderacionTotal;
        $neededScoreClean = max(0, min(100, round($neededScorePerPendingWeighted, 2)));

        foreach ($this->materiasData as $mIndex => $materia) {
            foreach ($materia['examenes'] as $eIndex => $exam) {
                if (empty($exam['es_real'])) {
                    $this->materiasData[$mIndex]['examenes'][$eIndex]['nota_simulada'] = $neededScoreClean;
                }
            }
        }
    }

    public function getSimulatedAdmissionStatusProperty()
    {
        if (! $this->postulante || ! $this->gestionActiva) {
            return 'reprobado';
        }

        $promedioProyectado = $this->promedioProyectado;

        // Si el promedio proyectado es menor a 60, queda reprobado automáticamente
        if ($promedioProyectado < 60.00) {
            return 'reprobado';
        }

        // Obtener todos los demás postulantes aprobados en la base de datos para esta gestión
        $postulantes = Postulante::where('gestion_id', $this->gestionActiva->id)
            ->where('id', '!=', $this->postulante->id)
            ->get();

        $aprobadosList = [];

        // Agregar los postulantes aprobados de la base de datos
        foreach ($postulantes as $p) {
            if ($p->nota_final >= 60.00) {
                $aprobadosList[] = [
                    'id' => $p->id,
                    'nota_final' => (float) $p->nota_final,
                    'carrera_primera_opcion_id' => $p->carrera_primera_opcion_id,
                    'carrera_segunda_opcion_id' => $p->carrera_segunda_opcion_id,
                ];
            }
        }

        // Agregar al postulante actual con su promedio simulado
        $aprobadosList[] = [
            'id' => $this->postulante->id,
            'nota_final' => (float) $promedioProyectado,
            'carrera_primera_opcion_id' => $this->postulante->carrera_primera_opcion_id,
            'carrera_segunda_opcion_id' => $this->postulante->carrera_segunda_opcion_id,
        ];

        // Ordenar todos por promedio descendente y luego por ID para desempate
        usort($aprobadosList, function ($a, $b) {
            if ($b['nota_final'] === $a['nota_final']) {
                return $a['id'] <=> $b['id'];
            }
            return $b['nota_final'] <=> $a['nota_final'];
        });

        // Cargar capacidades de cupos
        $carreras = Carrera::all();
        $capacidades1ra = [];
        $capacidades2da = [];
        $capacidadesTotal = [];
        $admitidos1raCounts = [];
        $admitidos2daCounts = [];
        $admitidosTotalCounts = [];

        $cupos = \App\Models\Cupo::where('gestion_id', $this->gestionActiva->id)->get()->keyBy('carrera_id');

        foreach ($carreras as $carrera) {
            $cupoObj = $cupos->get($carrera->id);
            $cap1 = $cupoObj ? $cupoObj->cantidad_primera_opcion : 150;
            $cap2 = $cupoObj ? $cupoObj->cantidad_segunda_opcion : 50;

            $capacidades1ra[$carrera->id] = $cap1;
            $capacidades2da[$carrera->id] = $cap2;
            $capacidadesTotal[$carrera->id] = $cap1 + $cap2;

            $admitidos1raCounts[$carrera->id] = 0;
            $admitidos2daCounts[$carrera->id] = 0;
            $admitidosTotalCounts[$carrera->id] = 0;
        }

        // Ejecutar simulación del proceso de admisión
        $statusActual = 'no_admitido';

        foreach ($aprobadosList as $p) {
            $c1 = $p['carrera_primera_opcion_id'];
            $c2 = $p['carrera_segunda_opcion_id'];
            $estadoAsignado = 'no_admitido';

            // Intentar primera opción
            if ($c1 && isset($capacidades1ra[$c1]) && $admitidos1raCounts[$c1] < $capacidades1ra[$c1] && $admitidosTotalCounts[$c1] < $capacidadesTotal[$c1]) {
                $estadoAsignado = 'admitido_primera_opcion';
                $admitidos1raCounts[$c1]++;
                $admitidosTotalCounts[$c1]++;
            }
            // Intentar segunda opción
            elseif ($c2 && isset($capacidades2da[$c2]) && $admitidos2daCounts[$c2] < $capacidades2da[$c2] && $admitidosTotalCounts[$c2] < $capacidadesTotal[$c2]) {
                $estadoAsignado = 'admitido_segunda_opcion';
                $admitidos2daCounts[$c2]++;
                $admitidosTotalCounts[$c2]++;
            }

            // Si es el postulante actual, guardamos su estado simulado
            if ($p['id'] === $this->postulante->id) {
                $statusActual = $estadoAsignado;
                break;
            }
        }

        return $statusActual;
    }

    public function render()
    {
        $layout = auth()->user() && auth()->user()->hasRole('Postulante') ? 'layouts.app' : 'layouts.admin';

        return view('livewire.calculadora-admision', [
            'promedioProyectado' => $this->promedioProyectado,
        ])->layout($layout, ['title' => 'Calculadora de Admisión - CUP']);
    }
}
