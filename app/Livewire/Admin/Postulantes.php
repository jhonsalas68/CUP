<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Postulante;
use App\Models\User;
use App\Models\Examen;
use Livewire\Component;
use Livewire\WithPagination;

class Postulantes extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCarrera = '';
    public $filterGestion = '';
    public $filterEstado = '';
    public $filterMateria = '';
    public $filterNotaMin = '';
    public $filterNotaMax = '';

    // Requisitos de inscripción
    public $ci_vigente = false;
    public $titulo_bachiller = false;
    public $libreta_legalizada = false;

    // Estado de pagos
    public $pago_realizado = false;
    public $pago_matricula_realizado = false;

    public $estadosOptions = [
        'pendiente'                => 'Pendiente',
        'admitido_primera_opcion'  => 'Admitido (1ra opción)',
        'admitido_segunda_opcion'  => 'Admitido (2da opción)',
        'aprobados'                => 'Aprobados (1ra y 2da opción)',
        'reprobado'                => 'Reprobado',
        'no_admitido'              => 'No admitido',
        'no_presentado'            => 'No presentado',
    ];

    public $estadosUpdateOptions = [
        'pendiente'                => 'Pendiente',
        'admitido_primera_opcion'  => 'Admitido (1ra opción)',
        'admitido_segunda_opcion'  => 'Admitido (2da opción)',
        'no_admitido'              => 'No admitido',
        'reprobado'                => 'Reprobado',
    ];

    // CRUD properties
    public $showModal = false;
    public $isEditing = false;
    public $postulanteId = null;

    // View Grades modal properties
    public $showNotasModal = false;
    public $selectedPostulante = null;
    public $postulanteNotas = [];

    // Form fields
    public $name = '';
    public $email = '';
    public $ci = '';
    public $telefono = '';
    public $fecha_nacimiento = '';
    public $sexo = '';
    public $direccion = '';
    public $colegio_procedencia = '';
    public $ciudad = '';
    public $carrera_primera_opcion_id = '';
    public $carrera_segunda_opcion_id = '';
    public $gestion_id = '';

    protected $rules = [
        'name'                       => 'required|string|max:255',
        'email'                      => 'required|email|max:255',
        'ci'                         => 'required|string|max:20',
        'telefono'                   => 'required|string|max:20',
        'fecha_nacimiento'           => 'required|date',
        'sexo'                       => 'required|string|in:M,F',
        'direccion'                  => 'required|string|max:255',
        'colegio_procedencia'        => 'required|string|max:255',
        'ciudad'                     => 'required|string|max:100',
        'carrera_primera_opcion_id'  => 'required|exists:carreras,id',
        'carrera_segunda_opcion_id'  => 'nullable|exists:carreras,id|different:carrera_primera_opcion_id',
        'gestion_id'                 => 'required|exists:gestiones,id',
        'ci_vigente'                 => 'boolean',
        'titulo_bachiller'           => 'boolean',
        'libreta_legalizada'         => 'boolean',
        'pago_realizado'             => 'boolean',
        'pago_matricula_realizado'   => 'boolean',
    ];

    public function mount()
    {
        if (!auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        // Default to active gestion
        $gestionActiva = Gestion::where('activo', true)->first();
        if ($gestionActiva) {
            $this->filterGestion = $gestionActiva->id;
        }
    }

    public function updatingSearch()    { $this->resetPage(); }
    public function updatingFilterCarrera() { $this->resetPage(); }
    public function updatingFilterGestion() { $this->resetPage(); }
    public function updatingFilterEstado()  { $this->resetPage(); }
    public function updatingFilterMateria() { $this->resetPage(); }
    public function updatingFilterNotaMin() { $this->resetPage(); }
    public function updatingFilterNotaMax() { $this->resetPage(); }

    public function cambiarEstado($id, $estado)
    {
        if (!array_key_exists($estado, $this->estadosUpdateOptions)) {
            session()->flash('error', 'Estado de admisión no válido para actualizar.');
            return;
        }

        $postulante = Postulante::findOrFail($id);
        $postulante->update(['estado_admision' => $estado]);
        
        // Recalculate score/admission state if state changes or has notes
        $examService = new \App\Services\ExamService();
        $examService->recalculatePostulanteScore($postulante->id, $postulante->gestion_id);

        session()->flash('message', 'Estado de postulante actualizado.');
    }

    public function openCreate()
    {
        $this->reset(['postulanteId', 'name', 'email', 'ci', 'telefono', 'fecha_nacimiento', 'sexo', 'direccion', 'colegio_procedencia', 'ciudad', 'carrera_primera_opcion_id', 'carrera_segunda_opcion_id', 'ci_vigente', 'titulo_bachiller', 'libreta_legalizada', 'pago_realizado', 'pago_matricula_realizado']);
        $this->resetValidation();
        $this->isEditing = false;

        $gestionActiva = Gestion::where('activo', true)->first();
        if ($gestionActiva) {
            $this->gestion_id = $gestionActiva->id;
        } else {
            $this->gestion_id = '';
        }

        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $this->resetValidation();
        $postulante = Postulante::with('user')->findOrFail($id);
        $this->postulanteId = $postulante->id;
        $this->name = $postulante->nombres_apellidos ?? $postulante->user?->name;
        $this->email = $postulante->user->email;
        $this->ci = $postulante->ci;
        $this->telefono = $postulante->telefono;
        $this->fecha_nacimiento = $postulante->fecha_nacimiento ? $postulante->fecha_nacimiento->format('Y-m-d') : '';
        $this->sexo = $postulante->sexo;
        $this->direccion = $postulante->direccion;
        $this->colegio_procedencia = $postulante->colegio_procedencia;
        $this->ciudad = $postulante->ciudad;
        $this->carrera_primera_opcion_id = $postulante->carrera_primera_opcion_id;
        $this->carrera_segunda_opcion_id = $postulante->carrera_segunda_opcion_id ?: '';
        $this->gestion_id = $postulante->gestion_id;
        $this->ci_vigente = (bool) $postulante->ci_vigente;
        $this->titulo_bachiller = (bool) $postulante->titulo_bachiller;
        $this->libreta_legalizada = (bool) $postulante->libreta_legalizada;
        $this->pago_realizado = (bool) $postulante->pago_realizado;
        $this->pago_matricula_realizado = (bool) $postulante->pago_matricula_realizado;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $emailRule = $this->isEditing
            ? 'required|email|max:255|unique:users,email,' . Postulante::findOrFail($this->postulanteId)->user_id
            : 'required|email|max:255|unique:users,email';

        $ciRule = $this->isEditing
            ? 'required|string|max:20|unique:postulantes,ci,' . $this->postulanteId
            : 'required|string|max:20|unique:postulantes,ci';

        $this->validate(array_merge($this->rules, [
            'email' => $emailRule,
            'ci' => $ciRule
        ]));

        $missingDocs = [];
        if (!$this->ci_vigente) $missingDocs[] = 'Cédula de Identidad Vigente';
        if (!$this->titulo_bachiller) $missingDocs[] = 'Título de Bachiller';
        if (!$this->libreta_legalizada) $missingDocs[] = 'Libreta Legalizada';

        $habilitado = empty($missingDocs);
        $mensaje_documentos = $habilitado ? null : 'Falta presentar: ' . implode(', ', $missingDocs);

        \Illuminate\Support\Facades\DB::transaction(function () use ($habilitado, $mensaje_documentos) {
            if ($this->isEditing) {
                $postulante = Postulante::findOrFail($this->postulanteId);
                $postulante->user->update([
                    'name'  => $this->name,
                    'email' => $this->email,
                ]);
                $postulante->update([
                    'nombres_apellidos'         => $this->name,
                    'ci'                        => $this->ci,
                    'telefono'                  => $this->telefono,
                    'fecha_nacimiento'          => $this->fecha_nacimiento,
                    'sexo'                      => $this->sexo,
                    'direccion'                 => $this->direccion,
                    'colegio_procedencia'       => $this->colegio_procedencia,
                    'ciudad'                    => $this->ciudad,
                    'carrera_primera_opcion_id' => $this->carrera_primera_opcion_id,
                    'carrera_segunda_opcion_id' => $this->carrera_segunda_opcion_id ?: null,
                    'gestion_id'                => $this->gestion_id,
                    'ci_vigente'                => (bool) $this->ci_vigente,
                    'titulo_bachiller'          => (bool) $this->titulo_bachiller,
                    'libreta_legalizada'        => (bool) $this->libreta_legalizada,
                    'pago_realizado'            => (bool) $this->pago_realizado,
                    'pago_matricula_realizado'  => (bool) $this->pago_matricula_realizado,
                    'habilitado'                => $habilitado,
                    'mensaje_documentos'        => $mensaje_documentos,
                ]);

                session()->flash('message', 'Postulante actualizado correctamente.');
            } else {
                $user = User::create([
                    'name'     => $this->name,
                    'email'    => $this->email,
                    'password' => bcrypt('password'),
                ]);
                $user->assignRole('Postulante');

                $postulante = Postulante::create([
                    'user_id'                   => $user->id,
                    'nombres_apellidos'         => $this->name,
                    'ci'                        => $this->ci,
                    'telefono'                  => $this->telefono,
                    'fecha_nacimiento'          => $this->fecha_nacimiento,
                    'sexo'                      => $this->sexo,
                    'direccion'                 => $this->direccion,
                    'colegio_procedencia'       => $this->colegio_procedencia,
                    'ciudad'                    => $this->ciudad,
                    'carrera_primera_opcion_id' => $this->carrera_primera_opcion_id,
                    'carrera_segunda_opcion_id' => $this->carrera_segunda_opcion_id ?: null,
                    'gestion_id'                => $this->gestion_id,
                    'estado_admision'           => 'pendiente',
                    'ci_vigente'                => (bool) $this->ci_vigente,
                    'titulo_bachiller'          => (bool) $this->titulo_bachiller,
                    'libreta_legalizada'        => (bool) $this->libreta_legalizada,
                    'pago_realizado'            => (bool) $this->pago_realizado,
                    'pago_matricula_realizado'  => (bool) $this->pago_matricula_realizado,
                    'habilitado'                => $habilitado,
                    'mensaje_documentos'        => $mensaje_documentos,
                ]);

                session()->flash('message', 'Postulante creado correctamente. Contraseña inicial: password');
            }

            // Recalculate score immediately
            $examService = new \App\Services\ExamService();
            $examService->recalculatePostulanteScore($postulante->id, $postulante->gestion_id);
        });

        $this->showModal = false;
        $this->reset(['postulanteId', 'name', 'email', 'ci', 'telefono', 'fecha_nacimiento', 'sexo', 'direccion', 'colegio_procedencia', 'ciudad', 'carrera_primera_opcion_id', 'carrera_segunda_opcion_id', 'gestion_id', 'ci_vigente', 'titulo_bachiller', 'libreta_legalizada', 'pago_realizado', 'pago_matricula_realizado']);
    }

    public function delete($id)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $postulante = Postulante::findOrFail($id);
            if ($postulante->user) {
                $postulante->user->delete();
            }
            $postulante->delete();
        });
        session()->flash('message', 'Postulante y su usuario asociado eliminados correctamente.');
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filterCarrera', 'filterGestion', 'filterEstado', 'filterMateria', 'filterNotaMin', 'filterNotaMax']);
        $this->resetPage();
    }

    public function processVoiceCommand($transcript)
    {
        $transcript = mb_strtolower($transcript, 'UTF-8');
        $transcript = $this->normalizeNumbers($transcript);
        
        // Limpieza / reinicio de filtros
        if (str_contains($transcript, 'limpiar') || str_contains($transcript, 'restablecer') || str_contains($transcript, 'todos') || str_contains($transcript, 'reiniciar') || str_contains($transcript, 'quitar')) {
            $this->reset(['search', 'filterCarrera', 'filterGestion', 'filterEstado', 'filterMateria', 'filterNotaMin', 'filterNotaMax']);
            session()->flash('voice_feedback', 'Filtros restablecidos.');
            $this->resetPage();
            return;
        }

        $feedback = [];

        // Parsear Carrera
        if (str_contains($transcript, 'sistemas') || str_contains($transcript, 'sistema')) {
            $c = Carrera::where('sigla', 'SIS')->first();
            if ($c) {
                $this->filterCarrera = $c->id;
                $feedback[] = 'Carrera: Sistemas';
            }
        } elseif (str_contains($transcript, 'informática') || str_contains($transcript, 'informatica')) {
            $c = Carrera::where('sigla', 'INF')->first();
            if ($c) {
                $this->filterCarrera = $c->id;
                $feedback[] = 'Carrera: Informática';
            }
        } elseif (str_contains($transcript, 'robótica') || str_contains($transcript, 'robotica')) {
            $c = Carrera::where('sigla', 'ROB')->first();
            if ($c) {
                $this->filterCarrera = $c->id;
                $feedback[] = 'Carrera: Robótica';
            }
        } elseif (str_contains($transcript, 'redes') || str_contains($transcript, 'telecomunicaciones')) {
            $c = Carrera::where('sigla', 'RED')->first();
            if ($c) {
                $this->filterCarrera = $c->id;
                $feedback[] = 'Carrera: Redes y Telecomunicaciones';
            }
        }

        // Parsear Gestión
        if (preg_match('/gestión\s+([a-z0-9\-]+)/', $transcript, $matches) || preg_match('/gestion\s+([a-z0-9\-]+)/', $transcript, $matches)) {
            $gestName = strtoupper($matches[1]);
            $g = Gestion::where('nombre', 'like', '%' . $gestName . '%')->first();
            if ($g) {
                $this->filterGestion = $g->id;
                $feedback[] = 'Gestión: ' . $g->nombre;
            }
        } elseif (preg_match('/(2025|2026)/', $transcript, $matches)) {
            $year = $matches[1];
            $g = Gestion::where('nombre', 'like', '%' . $year . '%')->first();
            if ($g) {
                $this->filterGestion = $g->id;
                $feedback[] = 'Gestión: ' . $g->nombre;
            }
        }

        // Parsear Estado
        if (str_contains($transcript, 'segunda opción') || str_contains($transcript, 'segunda opcion') || str_contains($transcript, 'segunda') || str_contains($transcript, '2da')) {
            $this->filterEstado = 'admitido_segunda_opcion';
            $feedback[] = 'Estado: Admitido (2da opción)';
        } elseif (str_contains($transcript, 'primera opción') || str_contains($transcript, 'primera opcion') || str_contains($transcript, 'primera') || str_contains($transcript, '1ra')) {
            $this->filterEstado = 'admitido_primera_opcion';
            $feedback[] = 'Estado: Admitido (1ra opción)';
        } elseif (str_contains($transcript, 'admitido') || str_contains($transcript, 'aprobado') || str_contains($transcript, 'pasaron') || str_contains($transcript, 'ingresó') || str_contains($transcript, 'ingreso') || str_contains($transcript, 'ingresaron')) {
            $this->filterEstado = 'aprobados';
            $feedback[] = 'Estado: Aprobados (1ra y 2da opción)';
        } elseif (str_contains($transcript, 'reprobado') || str_contains($transcript, 'reprobaron') || str_contains($transcript, 'desaprobado') || str_contains($transcript, 'no aprobado') || str_contains($transcript, 'rechazado') || str_contains($transcript, 'rechazados') || str_contains($transcript, 'reprobo')) {
            $this->filterEstado = 'reprobado';
            $feedback[] = 'Estado: Reprobado';
        } elseif (str_contains($transcript, 'pendiente') || str_contains($transcript, 'espera') || str_contains($transcript, 'revisión') || str_contains($transcript, 'revision')) {
            $this->filterEstado = 'pendiente';
            $feedback[] = 'Estado: Pendiente';
        } elseif (str_contains($transcript, 'no presentado') || str_contains($transcript, 'faltó') || str_contains($transcript, 'falto') || str_contains($transcript, 'no asistió') || str_contains($transcript, 'no asistio') || str_contains($transcript, 'inasistencia')) {
            $this->filterEstado = 'no_presentado';
            $feedback[] = 'Estado: No presentado';
        }

        // Parsear Materia
        if (str_contains($transcript, 'matemática') || str_contains($transcript, 'matematica')) {
            $m = Materia::where('nombre', 'like', '%Matemáticas%')
                ->when($this->filterCarrera, fn($query) => $query->where('carrera_id', $this->filterCarrera))
                ->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: ' . $m->nombre;
            }
        } elseif (str_contains($transcript, 'física') || str_contains($transcript, 'fisica')) {
            $m = Materia::where('nombre', 'like', '%Física%')
                ->when($this->filterCarrera, fn($query) => $query->where('carrera_id', $this->filterCarrera))
                ->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: ' . $m->nombre;
            }
        } elseif (str_contains($transcript, 'inglés') || str_contains($transcript, 'ingles')) {
            $m = Materia::where('nombre', 'like', '%Inglés%')
                ->when($this->filterCarrera, fn($query) => $query->where('carrera_id', $this->filterCarrera))
                ->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: ' . $m->nombre;
            }
        } elseif (str_contains($transcript, 'computación') || str_contains($transcript, 'computacion')) {
            $m = Materia::where('nombre', 'like', '%Computación%')
                ->when($this->filterCarrera, fn($query) => $query->where('carrera_id', $this->filterCarrera))
                ->first();
            if ($m) {
                $this->filterMateria = $m->id;
                $feedback[] = 'Materia: ' . $m->nombre;
            }
        }

        // Parsear Notas
        if (preg_match('/nota\s+(?:final\s+)?(?:mayor|superior|más\s+de|mas\s+de)\s+(?:a\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $feedback[] = 'Nota >= ' . $matches[1];
        } elseif (preg_match('/nota\s+(?:final\s+)?(?:menor|inferior|menos\s+de)\s+(?:a\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMax = $matches[1];
            $feedback[] = 'Nota <= ' . $matches[1];
        } elseif (preg_match('/nota\s+(?:final\s+)?entre\s+(\d+)\s+y\s+(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $this->filterNotaMax = $matches[2];
            $feedback[] = "Nota entre {$matches[1]} y {$matches[2]}";
        } elseif (preg_match('/nota\s+(?:final\s+)?(?:de\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $feedback[] = 'Nota >= ' . $matches[1];
        }

        // Búsqueda general (nombre o CI)
        if (preg_match('/(?:buscar|busca|nombre|ci|identidad)\s+([a-záéíóúñ0-9\s\-\.]+)/', $transcript, $matches)) {
            $this->search = trim($matches[1]);
            $feedback[] = 'Buscar: "' . $this->search . '"';
        }

        if (empty($feedback)) {
            session()->flash('voice_error', 'No se reconoció ningún criterio en: "' . $transcript . '"');
        } else {
            session()->flash('voice_feedback', 'Filtros aplicados: ' . implode(', ', $feedback));
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
            'sesenta' => 60, 'setenta' => 70, 'ochenta' => 80, 'noventa' => 90, 'cien' => 100
        ];
        
        $tens = [
            'treinta' => 30,
            'cuarenta' => 40,
            'cincuenta' => 50,
            'sesenta' => 60,
            'setenta' => 70,
            'ochenta' => 80,
            'noventa' => 90
        ];
        $units = [
            'uno' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4, 'cinco' => 5,
            'seis' => 6, 'siete' => 7, 'ocho' => 8, 'nueve' => 9
        ];
        
        foreach ($tens as $tenWord => $tenVal) {
            foreach ($units as $unitWord => $unitVal) {
                $text = preg_replace('/\b' . $tenWord . '\s+y\s+' . $unitWord . '\b/u', $tenVal + $unitVal, $text);
            }
        }
        
        foreach ($words as $word => $num) {
            $text = preg_replace('/\b' . $word . '\b/u', $num, $text);
        }
        
        return $text;
    }

    public function render()
    {
        $carreras = Carrera::orderBy('nombre')->get();
        $gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $materias = Materia::with('carrera')->orderBy('carrera_id')->orderBy('nombre')->get();

        $postulantes = Postulante::query()
            ->with(['user', 'carreraPrimeraOpn', 'carreraSegundaOpn', 'gestion'])
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->whereHas('user', fn($u) =>
                        $u->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                    )
                    ->orWhere('nombres_apellidos', 'like', '%' . $this->search . '%')
                    ->orWhere('ci', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCarrera, fn($q) =>
                $q->where(function ($inner) {
                    $inner->where('carrera_primera_opcion_id', $this->filterCarrera)
                          ->orWhere('carrera_segunda_opcion_id', $this->filterCarrera);
                })
            )
            ->when($this->filterGestion, fn($q) => $q->where('gestion_id', $this->filterGestion))
            ->when($this->filterEstado, function ($q) {
                if ($this->filterEstado === 'aprobados') {
                    $q->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion']);
                } else {
                    $q->where('estado_admision', $this->filterEstado);
                }
            })
            ->when($this->filterMateria, function ($q) {
                $q->whereHas('notas.examen', function ($sub) {
                    $sub->where('materia_id', $this->filterMateria);
                });
                
                if ($this->filterNotaMin !== '') {
                    $q->where(function ($query) {
                        $query->selectRaw('COALESCE(SUM(notas.puntaje * examenes.ponderacion / 100), 0)')
                              ->from('notas')
                              ->join('examenes', 'notas.examen_id', '=', 'examenes.id')
                              ->whereColumn('notas.postulante_id', 'postulantes.id')
                              ->where('examenes.materia_id', $this->filterMateria);
                    }, '>=', $this->filterNotaMin);
                }
                
                if ($this->filterNotaMax !== '') {
                    $q->where(function ($query) {
                        $query->selectRaw('COALESCE(SUM(notas.puntaje * examenes.ponderacion / 100), 0)')
                              ->from('notas')
                              ->join('examenes', 'notas.examen_id', '=', 'examenes.id')
                              ->whereColumn('notas.postulante_id', 'postulantes.id')
                              ->where('examenes.materia_id', $this->filterMateria);
                    }, '<=', $this->filterNotaMax);
                }
            })
            ->when(!$this->filterMateria && $this->filterNotaMin !== '', function ($q) {
                $q->where('nota_final', '>=', $this->filterNotaMin);
            })
            ->when(!$this->filterMateria && $this->filterNotaMax !== '', function ($q) {
                $q->where('nota_final', '<=', $this->filterNotaMax);
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.admin.postulantes', compact('postulantes', 'carreras', 'gestiones', 'materias'))
            ->layout('layouts.admin');
    }

    public function openNotas($id)
    {
        $this->selectedPostulante = Postulante::with([
            'carreraPrimeraOpn',
            'notas.examen.materia'
        ])->findOrFail($id);

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

            $this->postulanteNotas[] = [
                'materia_nombre' => $materia->nombre,
                'primer_parcial' => $notasMateria['Primer Parcial'],
                'segundo_parcial' => $notasMateria['Segundo Parcial'],
                'examen_final' => $notasMateria['Examen Final'],
                'total_materia' => round($notaMateriaAcumulada, 2)
            ];
        }

        $this->showNotasModal = true;
    }
}
