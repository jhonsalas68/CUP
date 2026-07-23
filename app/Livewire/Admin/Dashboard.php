<?php

namespace App\Livewire\Admin;

use App\Exceptions\AdmissionSelectionException;
use App\Mail\AdmissionResultMail;
use App\Models\Bitacora;
use App\Models\Carrera;
use App\Models\Cupo;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use App\Services\AdmissionSelectionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public $selectedGestionId;

    public $gestiones = [];

    // comparison stats
    public $compareGestionId;

    public $currentAprobados = 0;

    public $comparePostulantes = 0;

    public $compareAdmitidos = 0;

    public $compareAprobados = 0;

    public $compareGestionNombre = '';

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
        if (! auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        // Cache static list catalogs for 10 minutes to save roundtrips
        $this->gestiones = Cache::remember('dashboard_gestiones_list', 600, function () {
            return Gestion::orderBy('fecha_inicio', 'desc')->get();
        });

        $active = $this->gestiones->where('activo', true)->first() ?? $this->gestiones->first();
        $this->selectedGestionId = $active ? $active->id : null;

        $this->carrerasList = Cache::remember('dashboard_carreras_list', 600, function () {
            return Carrera::orderBy('nombre')->get();
        });

        $this->exportGestionId = $this->selectedGestionId;

        $compare = $this->gestiones->where('id', '!=', $this->selectedGestionId)->first();
        $this->compareGestionId = $compare ? $compare->id : null;

        $this->loadStats();
    }

    public function updatedSelectedGestionId()
    {
        if ($this->selectedGestionId == $this->compareGestionId) {
            $compare = $this->gestiones->where('id', '!=', $this->selectedGestionId)->first();
            $this->compareGestionId = $compare ? $compare->id : null;
        }
        $this->loadStats();
        // Dispatch event for charts reload
        $this->dispatch('stats-updated', current: $this->currentStats(), compare: $this->compareStats());
    }

    public function loadStats()
    {
        if (! $this->selectedGestionId) {
            return;
        }

        $cacheKey = 'dashboard_stats_'.$this->selectedGestionId;

        // Cache main stats for 2 minutes
        $stats = Cache::remember($cacheKey, 120, function () {
            // 1. KPIs (7 queries combined into 1)
            $kpiStats = \DB::selectOne("
                SELECT 
                    (SELECT COUNT(*) FROM postulantes WHERE gestion_id = :g1) as total_postulantes,
                    (SELECT COUNT(*) FROM postulantes WHERE gestion_id = :g2 AND estado_admision IN ('admitido_primera_opcion', 'admitido_segunda_opcion')) as total_admitidos,
                    (SELECT COUNT(*) FROM postulantes WHERE gestion_id = :g3 AND estado_admision = 'reprobado') as total_reprobados,
                    (SELECT COUNT(*) FROM postulantes WHERE gestion_id = :g4 AND estado_admision IN ('admitido_primera_opcion', 'admitido_segunda_opcion', 'no_admitido')) as current_aprobados,
                    (SELECT COALESCE(SUM(cantidad_primera_opcion + cantidad_segunda_opcion), 0) FROM cupos WHERE gestion_id = :g5) as total_cupos,
                    (SELECT COUNT(*) FROM grupos WHERE gestion_id = :g6) as total_grupos
            ", [
                'g1' => $this->selectedGestionId,
                'g2' => $this->selectedGestionId,
                'g3' => $this->selectedGestionId,
                'g4' => $this->selectedGestionId,
                'g5' => $this->selectedGestionId,
                'g6' => $this->selectedGestionId,
            ]);

            // 2. Carreras más demandadas (joins Carrera to get sigla and count in one go)
            $demanda = Postulante::where('postulantes.gestion_id', $this->selectedGestionId)
                ->join('carreras', 'postulantes.carrera_primera_opcion_id', '=', 'carreras.id')
                ->select('carreras.sigla', \DB::raw('count(*) as total'))
                ->groupBy('carreras.sigla')
                ->orderBy('total', 'desc')
                ->get();

            // 3. Rendimiento por grupo + Docente name directly in query via STRING_AGG / GROUP_CONCAT
            $driver = \DB::connection()->getDriverName();
            $docenteConcat = $driver === 'sqlite' ? "group_concat(d.nombre, ', ')" : "STRING_AGG(d.nombre, ', ')";

            $statsRaw = \DB::select("
                SELECT 
                    g.id as grupo_id,
                    g.nombre as grupo_nombre,
                    m.nombre as materia_nombre,
                    COUNT(pg.postulante_id) as total_alumnos,
                    COALESCE(AVG(student_grades.nota_materia), 0) as promedio_grupo,
                    COALESCE(SUM(CASE WHEN student_grades.nota_materia >= 60.00 THEN 1 ELSE 0 END), 0) as aprobados,
                    COALESCE((
                        SELECT {$docenteConcat}
                        FROM asignaciones_docente ad 
                        JOIN docentes d ON ad.docente_id = d.id 
                        WHERE ad.grupo_id = g.id
                    ), 'No asignado') as docente_nombre
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

            $gruposRendimiento = [];
            foreach ($statsRaw as $row) {
                $total = (int) $row->total_alumnos;
                $aprobados = (int) $row->aprobados;

                $promedioGrupo = round((float) $row->promedio_grupo, 2);
                $porcentajeAprobacion = $total > 0 ? round(($aprobados / $total) * 100, 2) : 0.00;

                $gruposRendimiento[] = [
                    'id' => $row->grupo_id,
                    'nombre' => $row->grupo_nombre,
                    'materia' => $row->materia_nombre,
                    'docente' => $row->docente_nombre,
                    'total_alumnos' => $total,
                    'promedio' => $promedioGrupo,
                    'tasa_aprobacion' => $porcentajeAprobacion,
                ];
            }

            // 4. Estadísticas Históricas (LEFT JOIN query combining all metrics)
            $historico = \DB::select("
                SELECT 
                    g.nombre as gestion_nombre,
                    COUNT(p.id) as total_postulantes,
                    COUNT(CASE WHEN p.estado_admision IN ('admitido_primera_opcion', 'admitido_segunda_opcion') THEN 1 END) as total_admitidos
                FROM gestiones g
                LEFT JOIN postulantes p ON g.id = p.gestion_id
                GROUP BY g.id, g.nombre, g.fecha_inicio
                ORDER BY g.fecha_inicio ASC
            ");

            $historicoLabels = [];
            $historicoPostulantes = [];
            $historicoAdmitidos = [];

            foreach ($historico as $row) {
                $historicoLabels[] = $row->gestion_nombre;
                $historicoPostulantes[] = (int) $row->total_postulantes;
                $historicoAdmitidos[] = (int) $row->total_admitidos;
            }

            return [
                'totalPostulantes' => (int) $kpiStats->total_postulantes,
                'totalAdmitidos' => (int) $kpiStats->total_admitidos,
                'totalReprobados' => (int) $kpiStats->total_reprobados,
                'totalCuposDisponibles' => (float) $kpiStats->total_cupos,
                'totalGrupos' => (int) $kpiStats->total_grupos,
                'currentAprobados' => (int) $kpiStats->current_aprobados,
                'carrerasLabels' => $demanda->pluck('sigla')->toArray(),
                'carrerasValues' => $demanda->pluck('total')->toArray(),
                'gruposRendimiento' => $gruposRendimiento,
                'historicoLabels' => $historicoLabels,
                'historicoPostulantes' => $historicoPostulantes,
                'historicoAdmitidos' => $historicoAdmitidos,
            ];
        });

        // Hydrate public properties
        $this->totalPostulantes = $stats['totalPostulantes'];
        $this->totalAdmitidos = $stats['totalAdmitidos'];
        $this->totalReprobados = $stats['totalReprobados'];
        $this->totalCuposDisponibles = $stats['totalCuposDisponibles'];
        $this->totalCuposOcupados = $this->totalAdmitidos;
        $this->totalGrupos = $stats['totalGrupos'];
        $this->currentAprobados = $stats['currentAprobados'];
        $this->carrerasLabels = $stats['carrerasLabels'];
        $this->carrerasValues = $stats['carrerasValues'];
        $this->gruposRendimiento = $stats['gruposRendimiento'];
        $this->historicoLabels = $stats['historicoLabels'];
        $this->historicoPostulantes = $stats['historicoPostulantes'];
        $this->historicoAdmitidos = $stats['historicoAdmitidos'];

        if (! $this->selectedDetailCarreraId && count($this->carrerasList) > 0) {
            $this->selectedDetailCarreraId = $this->carrerasList->first()->id;
        }
        $this->loadAdmitidosDetalle();
        $this->loadCompareStats();
    }

    public function openAdmissionProcess()
    {
        $this->reset(['admissionError', 'admissionStats', 'isProcessing']);
        $this->showAdmissionModal = true;
    }

    public function runAdmissionProcess(AdmissionSelectionService $service)
    {
        $this->isProcessing = true;
        $this->admissionError = null;
        $this->admissionStats = null;

        try {
            $res = $service->processAdmissions($this->selectedGestionId);

            if ($res['success']) {
                // Load fresh stats for summary
                $this->admissionStats = $service->getStats($this->selectedGestionId);

                // Clear the cache keys so stats refresh
                Cache::forget('dashboard_stats_'.$this->selectedGestionId);
                Cache::forget('dashboard_compare_'.$this->selectedGestionId);
                foreach ($this->carrerasList as $c) {
                    Cache::forget('dashboard_admitidos_'.$this->selectedGestionId.'_'.$c->id);
                }

                // Log activity
                $gestionNombre = $this->gestiones->where('id', $this->selectedGestionId)->first()?->nombre ?? $this->selectedGestionId;
                Bitacora::create([
                    'user_id' => auth()->id(),
                    'action' => 'proceso_admision',
                    'objeto' => 'Proceso Admisión',
                    'descripcion' => "Se ejecutó el proceso de admisión y asignación de cupos para la gestión '{$gestionNombre}'",
                    'payload' => [
                        'stats' => $this->admissionStats['general'] ?? [],
                    ],
                    'ip_address' => request()->ip(),
                ]);

                // Reload parent dashboard numbers
                $this->loadStats();

                // Dispatch event to refresh charts
                $this->dispatch('stats-updated', current: $this->currentStats(), compare: $this->compareStats());
            } else {
                $this->admissionError = 'El proceso no pudo completarse correctamente.';
            }
        } catch (AdmissionSelectionException $e) {
            $this->admissionError = $e->getMessage();
        } catch (\Exception $e) {
            $this->admissionError = 'Ocurrió un error inesperado al procesar las admisiones: '.$e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function showGroupDetails($groupId)
    {
        $this->reset(['selectedGroupInfo', 'groupAlumnos']);

        $grupo = Grupo::with(['materia', 'docentes.user', 'postulantes.user'])
            ->findOrFail($groupId);

        $docenteNombre = $grupo->docentes->first() ? ($grupo->docentes->first()->nombre ?? $grupo->docentes->first()->user->name) : 'No asignado';

        $this->selectedGroupInfo = [
            'id' => $grupo->id,
            'nombre' => $grupo->nombre,
            'materia' => $grupo->materia->nombre,
            'docente' => $docenteNombre,
        ];

        // Optimized 3-query calculation of student grades in that group's subject
        $exams = Examen::where('materia_id', $grupo->materia_id)
            ->where('gestion_id', $this->selectedGestionId)
            ->get();

        $notas = Nota::whereIn('examen_id', $exams->pluck('id'))
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
        if (! $this->selectedGestionId || ! $this->selectedDetailCarreraId) {
            $this->admitidosDetalle = [];

            return;
        }

        $cacheKey = 'dashboard_admitidos_'.$this->selectedGestionId.'_'.$this->selectedDetailCarreraId;

        $this->admitidosDetalle = Cache::remember($cacheKey, 120, function () {
            return Postulante::where('gestion_id', $this->selectedGestionId)
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
        });
    }

    public function updatedSelectedDetailCarreraId()
    {
        $this->loadAdmitidosDetalle();
    }

    public function sendEmailNotifications()
    {
        if (! auth()->user()->hasRole('Administrador')) {
            abort(403);
        }

        $postulantes = Postulante::with('user')
            ->where('gestion_id', $this->selectedGestionId)
            ->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion', 'no_admitido', 'reprobado'])
            ->get();

        if ($postulantes->isEmpty()) {
            session()->flash('error', 'No hay postulantes con resultados para notificar en esta gestión.');

            return;
        }

        $count = 0;
        foreach ($postulantes as $postulante) {
            if ($postulante->user && $postulante->user->email) {
                Mail::to($postulante->user->email)
                    ->queue(new AdmissionResultMail($postulante));
                $count++;
            }
        }

        // Log this action to Bitacora
        $gestionNombre = $this->gestiones->where('id', $this->selectedGestionId)->first()?->nombre ?? $this->selectedGestionId;
        Bitacora::create([
            'user_id' => auth()->id(),
            'action' => 'proceso_admision',
            'objeto' => 'Notificaciones Gmail',
            'descripcion' => "Se encolaron {$count} notificaciones por correo electrónico (Gmail SMTP) a los postulantes de la gestión '{$gestionNombre}'",
            'payload' => [
                'gestion_id' => $this->selectedGestionId,
                'cantidad_notificados' => $count,
            ],
            'ip_address' => request()->ip(),
        ]);

        session()->flash('message', "¡Proceso de envío masivo iniciado! Se han encolado {$count} correos en segundo plano de forma segura. Para procesar el envío, ejecuta 'php artisan queue:work' en tu terminal.");
    }

    public function sendTestEmail()
    {
        if (! auth()->user()->hasRole('Administrador')) {
            abort(403);
        }

        $adminEmail = auth()->user()->email;
        if (! $adminEmail) {
            $adminEmail = 'jssalasr126@ficct.uagrm.edu.bo';
        }

        // Get first postulante to populate template or create a mock
        $postulante = Postulante::first();
        if (! $postulante) {
            $postulante = new Postulante([
                'nombres_apellidos' => 'Usuario de Prueba',
                'ci' => '1234567',
                'estado_admision' => 'admitido_primera_opcion',
                'nota_final' => 85.00,
            ]);
        }

        try {
            Mail::to($adminEmail)
                ->send(new AdmissionResultMail($postulante));

            // Log test email
            Bitacora::create([
                'user_id' => auth()->id(),
                'action' => 'proceso_admision',
                'objeto' => 'Correo de Prueba',
                'descripcion' => "Se envió un correo de prueba SMTP exitoso a {$adminEmail}",
                'ip_address' => request()->ip(),
            ]);

            session()->flash('message', "¡Conexión SMTP de Gmail exitosa! Se envió un correo de prueba correctamente a {$adminEmail}. Verifica tu bandeja de entrada.");
        } catch (\Exception $e) {
            session()->flash('error', 'Fallo en la conexión SMTP de Gmail. Razón: '.$e->getMessage());
        }
    }

    public function updatedCompareGestionId()
    {
        $this->loadCompareStats();
        $this->dispatch('stats-updated', current: $this->currentStats(), compare: $this->compareStats());
    }

    public function loadCompareStats()
    {
        if (! $this->compareGestionId) {
            $this->comparePostulantes = 0;
            $this->compareAdmitidos = 0;
            $this->compareAprobados = 0;
            $this->compareGestionNombre = 'N/A';

            return;
        }

        $compareGestion = $this->gestiones->where('id', $this->compareGestionId)->first();
        $this->compareGestionNombre = $compareGestion ? $compareGestion->nombre : 'N/A';

        $cacheKey = 'dashboard_compare_'.$this->compareGestionId;
        $compareStats = Cache::remember($cacheKey, 120, function () {
            return Postulante::where('gestion_id', $this->compareGestionId)
                ->selectRaw("
                    COUNT(*) as total,
                    COUNT(CASE WHEN estado_admision IN ('admitido_primera_opcion', 'admitido_segunda_opcion') THEN 1 END) as admitidos,
                    COUNT(CASE WHEN estado_admision IN ('admitido_primera_opcion', 'admitido_segunda_opcion', 'no_admitido') THEN 1 END) as aprobados
                ")
                ->first();
        });

        $this->comparePostulantes = (int) $compareStats->total;
        $this->compareAdmitidos = (int) $compareStats->admitidos;
        $this->compareAprobados = (int) $compareStats->aprobados;
    }

    #[Computed]
    public function currentStats()
    {
        $currentGestion = $this->gestiones->where('id', $this->selectedGestionId)->first();

        return [
            'nombre' => $currentGestion ? $currentGestion->nombre : 'Gestión Seleccionada',
            'postulantes' => $this->totalPostulantes,
            'aprobados' => $this->currentAprobados,
            'admitidos' => $this->totalAdmitidos,
        ];
    }

    #[Computed]
    public function compareStats()
    {
        return [
            'nombre' => $this->compareGestionNombre ?: 'Sin comparar',
            'postulantes' => $this->comparePostulantes,
            'aprobados' => $this->compareAprobados,
            'admitidos' => $this->compareAdmitidos,
        ];
    }

    // What-if simulator properties
    public $showSimulationModal = false;

    public $simNotaMinima = 60.00;

    public $simCupos = [];

    public $simulationStats = null;

    public $isSimulating = false;

    public function openSimulation()
    {
        $this->reset(['simulationStats', 'isSimulating']);
        $this->simNotaMinima = 60.00;

        $this->simCupos = [];
        $carreras = Carrera::all();
        foreach ($carreras as $carrera) {
            $cupo = Cupo::where('carrera_id', $carrera->id)
                ->where('gestion_id', $this->selectedGestionId)
                ->first();
            $this->simCupos[$carrera->id] = [
                'sigla' => $carrera->sigla,
                'nombre' => $carrera->nombre,
                'primera' => $cupo ? $cupo->cantidad_primera_opcion : 10,
                'segunda' => $cupo ? $cupo->cantidad_segunda_opcion : 5,
            ];
        }

        $this->showSimulationModal = true;
    }

    public function runSimulation()
    {
        $this->isSimulating = true;
        $this->simulationStats = null;

        try {
            $carreras = Carrera::all();
            $postulantes = Postulante::where('gestion_id', $this->selectedGestionId)->get();

            if ($postulantes->isEmpty()) {
                $this->simulationStats = [
                    'error' => 'No existen postulantes registrados para esta gestión.',
                ];
                $this->isSimulating = false;

                return;
            }

            $service = new AdmissionSelectionService;

            // Preload all data in simulation to prevent N+1 queries (5000x speedup)
            $preloadedMaterias = Materia::all()->groupBy('carrera_id');

            $allExams = Examen::where('gestion_id', $this->selectedGestionId)->get();
            $preloadedExams = $allExams->groupBy('materia_id');

            $allNotas = Nota::whereIn('examen_id', $allExams->pluck('id'))->get();
            $preloadedNotas = [];
            foreach ($allNotas as $n) {
                $preloadedNotas[$n->postulante_id][$n->examen_id] = $n;
            }

            $aprobadosMap = [];
            $reprobadosCount = 0;
            $pendientesCount = 0;
            $evaluados = [];

            foreach ($postulantes as $postulante) {
                $eval = $service->evaluatePostulante($postulante, $this->selectedGestionId, $preloadedMaterias, $preloadedExams, $preloadedNotas, $this->simNotaMinima);

                if ($eval['has_pending_exams']) {
                    $pendientesCount++;
                }

                $notaFinal = $eval['nota_final'];
                $aprobado = ($notaFinal >= $this->simNotaMinima) && ! $eval['has_pending_exams'];

                $evaluados[] = [
                    'id' => $postulante->id,
                    'nombres_apellidos' => $postulante->nombres_apellidos,
                    'ci' => $postulante->ci,
                    'nota_final' => $notaFinal,
                    'carrera_primera_opcion_id' => $postulante->carrera_primera_opcion_id,
                    'carrera_segunda_opcion_id' => $postulante->carrera_segunda_opcion_id,
                    'aprobado' => $aprobado,
                    'has_pending_exams' => $eval['has_pending_exams'],
                ];

                if ($eval['has_pending_exams']) {
                    // omit from rankings
                } elseif (! $aprobado) {
                    $reprobadosCount++;
                } else {
                    $aprobadosMap[$postulante->carrera_primera_opcion_id][] = [
                        'id' => $postulante->id,
                        'nombres_apellidos' => $postulante->nombres_apellidos,
                        'ci' => $postulante->ci,
                        'nota_final' => $notaFinal,
                        'carrera_primera_opcion_id' => $postulante->carrera_primera_opcion_id,
                        'carrera_segunda_opcion_id' => $postulante->carrera_segunda_opcion_id,
                    ];
                }
            }

            // 1ra Opción Ranking
            $noAdmitidos1ra = [];
            $admitidos1raMap = [];

            foreach ($carreras as $carrera) {
                $cupoLimit = $this->simCupos[$carrera->id]['primera'] ?? 0;
                $candidatos = $aprobadosMap[$carrera->id] ?? [];

                usort($candidatos, function ($a, $b) {
                    if ($b['nota_final'] === $a['nota_final']) {
                        return $a['id'] <=> $b['id'];
                    }

                    return $b['nota_final'] <=> $a['nota_final'];
                });

                $admitidos = array_slice($candidatos, 0, $cupoLimit);
                $noAdmitidos = array_slice($candidatos, $cupoLimit);

                $admitidos1raMap[$carrera->id] = $admitidos;
                foreach ($noAdmitidos as $na) {
                    $noAdmitidos1ra[] = $na;
                }
            }

            // 2da Opción Ranking
            $candidatos2daMap = [];
            foreach ($noAdmitidos1ra as $na) {
                if ($na['carrera_segunda_opcion_id']) {
                    $candidatos2daMap[$na['carrera_segunda_opcion_id']][] = $na;
                }
            }

            $admitidos2daMap = [];
            $finalNoAdmitidosMap = [];

            foreach ($carreras as $carrera) {
                $cupoLimit = $this->simCupos[$carrera->id]['segunda'] ?? 0;
                $candidatos = $candidatos2daMap[$carrera->id] ?? [];

                usort($candidatos, function ($a, $b) {
                    if ($b['nota_final'] === $a['nota_final']) {
                        return $a['id'] <=> $b['id'];
                    }

                    return $b['nota_final'] <=> $a['nota_final'];
                });

                $admitidos = array_slice($candidatos, 0, $cupoLimit);
                $noAdmitidos = array_slice($candidatos, $cupoLimit);

                $admitidos2daMap[$carrera->id] = $admitidos;
                $finalNoAdmitidosMap[$carrera->id] = $noAdmitidos;
            }

            // Compile simulated stats
            $carrerasStats = [];
            $totalAdmitidos1ra = 0;
            $totalAdmitidos2da = 0;
            $totalNoAdmitidos = 0;

            foreach ($carreras as $carrera) {
                $adm1 = count($admitidos1raMap[$carrera->id] ?? []);
                $adm2 = count($admitidos2daMap[$carrera->id] ?? []);
                $noAdm = count($finalNoAdmitidosMap[$carrera->id] ?? []);

                $noOptRegisteredApprovedCount = 0;
                foreach ($noAdmitidos1ra as $na) {
                    if (! $na['carrera_segunda_opcion_id'] && $na['carrera_primera_opcion_id'] == $carrera->id) {
                        $noOptRegisteredApprovedCount++;
                    }
                }
                $noAdm += $noOptRegisteredApprovedCount;

                $totalAdmitidos1ra += $adm1;
                $totalAdmitidos2da += $adm2;
                $totalNoAdmitidos += $noAdm;

                $allSimAdmittedNotes = [];
                foreach (($admitidos1raMap[$carrera->id] ?? []) as $a) {
                    $allSimAdmittedNotes[] = $a['nota_final'];
                }
                foreach (($admitidos2daMap[$carrera->id] ?? []) as $a) {
                    $allSimAdmittedNotes[] = $a['nota_final'];
                }

                $minNota = count($allSimAdmittedNotes) > 0 ? min($allSimAdmittedNotes) : 0.00;
                $maxNota = count($allSimAdmittedNotes) > 0 ? max($allSimAdmittedNotes) : 0.00;
                $avgNota = count($allSimAdmittedNotes) > 0 ? (array_sum($allSimAdmittedNotes) / count($allSimAdmittedNotes)) : 0.00;

                $carrerasStats[$carrera->sigla] = [
                    'nombre' => $carrera->nombre,
                    'inscritos_primera_opcion' => Postulante::where('gestion_id', $this->selectedGestionId)->where('carrera_primera_opcion_id', $carrera->id)->count(),
                    'cupo_primera_opcion' => $this->simCupos[$carrera->id]['primera'],
                    'cupo_segunda_opcion' => $this->simCupos[$carrera->id]['segunda'],
                    'admitidos_primera_opcion' => $adm1,
                    'admitidos_segunda_opcion' => $adm2,
                    'no_admitidos' => $noAdm,
                    'nota_minima_ingreso' => round($minNota, 2),
                    'nota_maxima_ingreso' => round($maxNota, 2),
                    'nota_promedio_ingreso' => round($avgNota, 2),
                ];
            }

            $totalPost = count($postulantes);
            $totalAdm = $totalAdmitidos1ra + $totalAdmitidos2da;

            $this->simulationStats = [
                'success' => true,
                'general' => [
                    'total_postulantes' => $totalPost,
                    'pendientes' => $pendientesCount,
                    'reprobados' => $reprobadosCount,
                    'no_admitidos' => $totalNoAdmitidos,
                    'admitidos_primera_opcion' => $totalAdmitidos1ra,
                    'admitidos_segunda_opcion' => $totalAdmitidos2da,
                    'total_admitidos' => $totalAdm,
                    'tasa_aprobacion' => $totalPost > 0 ? round((($totalPost - $reprobadosCount - $pendientesCount) / $totalPost) * 100, 2) : 0.00,
                    'tasa_admision' => $totalPost > 0 ? round(($totalAdm / $totalPost) * 100, 2) : 0.00,
                ],
                'carreras' => $carrerasStats,
            ];

            // Registrar en bitácora la simulación
            $gestionNombre = $this->gestiones->where('id', $this->selectedGestionId)->first()?->nombre ?? $this->selectedGestionId;
            Bitacora::create([
                'user_id' => auth()->id(),
                'action' => 'simulacion_admision',
                'objeto' => 'Simulador CUP',
                'descripcion' => "Se ejecutó una simulación de admisión para la gestión '{$gestionNombre}' con nota mínima de {$this->simNotaMinima}",
                'payload' => [
                    'sim_nota_minima' => $this->simNotaMinima,
                    'sim_cupos' => $this->simCupos,
                    'stats' => $this->simulationStats['general'],
                ],
                'ip_address' => request()->ip(),
            ]);

        } catch (\Exception $e) {
            $this->simulationStats = [
                'error' => 'Ocurrió un error al procesar la simulación: '.$e->getMessage(),
            ];
        } finally {
            $this->isSimulating = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('layouts.admin');
    }
}
