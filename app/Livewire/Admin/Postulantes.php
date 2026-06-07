<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Postulante;
use App\Models\User;
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

    public $estadosOptions = [
        'pendiente'                => 'Pendiente',
        'admitido_primera_opcion'  => 'Admitido (1ra opción)',
        'admitido_segunda_opcion'  => 'Admitido (2da opción)',
        'reprobado'                => 'Reprobado',
        'no_presentado'            => 'No presentado',
    ];

    // CRUD properties
    public $showModal = false;
    public $isEditing = false;
    public $postulanteId = null;

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
        $postulante = Postulante::findOrFail($id);
        $postulante->update(['estado_admision' => $estado]);
        
        // Recalculate score/admission state if state changes or has notes
        $examService = new \App\Services\ExamService();
        $examService->recalculatePostulanteScore($postulante->id, $postulante->gestion_id);

        session()->flash('message', 'Estado de postulante actualizado.');
    }

    public function openCreate()
    {
        $this->reset(['postulanteId', 'name', 'email', 'ci', 'telefono', 'fecha_nacimiento', 'sexo', 'direccion', 'colegio_procedencia', 'ciudad', 'carrera_primera_opcion_id', 'carrera_segunda_opcion_id', 'ci_vigente', 'titulo_bachiller', 'libreta_legalizada']);
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
        $this->name = $postulante->user->name;
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

        \Illuminate\Support\Facades\DB::transaction(function () {
            if ($this->isEditing) {
                $postulante = Postulante::findOrFail($this->postulanteId);
                $postulante->user->update([
                    'name'  => $this->name,
                    'email' => $this->email,
                ]);
                $postulante->update([
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
                ]);

                session()->flash('message', 'Postulante creado correctamente. Contraseña inicial: password');
            }

            // Recalculate score immediately
            $examService = new \App\Services\ExamService();
            $examService->recalculatePostulanteScore($postulante->id, $postulante->gestion_id);
        });

        $this->showModal = false;
        $this->reset(['postulanteId', 'name', 'email', 'ci', 'telefono', 'fecha_nacimiento', 'sexo', 'direccion', 'colegio_procedencia', 'ciudad', 'carrera_primera_opcion_id', 'carrera_segunda_opcion_id', 'gestion_id', 'ci_vigente', 'titulo_bachiller', 'libreta_legalizada']);
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

    public function processVoiceCommand($transcript)
    {
        $transcript = mb_strtolower($transcript, 'UTF-8');
        
        // Limpieza / reinicio de filtros
        if (str_contains($transcript, 'limpiar') || str_contains($transcript, 'restablecer') || str_contains($transcript, 'todos') || str_contains($transcript, 'reiniciar') || str_contains($transcript, 'quitar')) {
            $this->reset(['search', 'filterCarrera', 'filterGestion', 'filterEstado', 'filterMateria', 'filterNotaMin', 'filterNotaMax']);
            session()->flash('voice_feedback', 'Filtros restablecidos.');
            $this->resetPage();
            return;
        }

        $feedback = [];

        // Parsear Carrera
        if (str_contains($transcript, 'sistema')) {
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
        if (str_contains($transcript, 'admitido') || str_contains($transcript, 'aprobado') || str_contains($transcript, 'pasaron')) {
            $this->filterEstado = 'admitido_primera_opcion';
            $feedback[] = 'Estado: Admitido';
        } elseif (str_contains($transcript, 'reprobado') || str_contains($transcript, 'reprobaron') || str_contains($transcript, 'desaprobado')) {
            $this->filterEstado = 'reprobado';
            $feedback[] = 'Estado: Reprobado';
        } elseif (str_contains($transcript, 'pendiente') || str_contains($transcript, 'espera')) {
            $this->filterEstado = 'pendiente';
            $feedback[] = 'Estado: Pendiente';
        } elseif (str_contains($transcript, 'no presentado') || str_contains($transcript, 'faltó') || str_contains($transcript, 'falto')) {
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
        if (preg_match('/nota\s+(?:mayor|superior|más\s+de|mas\s+de)\s+(?:a\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $feedback[] = 'Nota >= ' . $matches[1];
        } elseif (preg_match('/nota\s+(?:menor|inferior|menos\s+de)\s+(?:a\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMax = $matches[1];
            $feedback[] = 'Nota <= ' . $matches[1];
        } elseif (preg_match('/nota\s+entre\s+(\d+)\s+y\s+(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $this->filterNotaMax = $matches[2];
            $feedback[] = "Nota entre {$matches[1]} y {$matches[2]}";
        } elseif (preg_match('/nota\s+(?:de\s+)?(\d+)/', $transcript, $matches)) {
            $this->filterNotaMin = $matches[1];
            $feedback[] = 'Nota >= ' . $matches[1];
        }

        // Búsqueda general
        if (preg_match('/(?:buscar|busca|nombre)\s+([a-záéíóúñ0-9]+)/', $transcript, $matches)) {
            $this->search = $matches[1];
            $feedback[] = 'Buscar: "' . $this->search . '"';
        }

        if (empty($feedback)) {
            session()->flash('voice_error', 'No se reconoció ningún criterio en: "' . $transcript . '"');
        } else {
            session()->flash('voice_feedback', 'Filtros aplicados: ' . implode(', ', $feedback));
        }

        $this->resetPage();
    }

    public function render()
    {
        $carreras = Carrera::orderBy('nombre')->get();
        $gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $materias = Materia::with('carrera')->orderBy('carrera_id')->orderBy('nombre')->get();

        $postulantes = Postulante::query()
            ->with(['user', 'carreraPrimeraOpn', 'carreraSegundaOpn', 'gestion'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($u) =>
                    $u->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                )->orWhere('ci', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterCarrera, fn($q) =>
                $q->where(function ($inner) {
                    $inner->where('carrera_primera_opcion_id', $this->filterCarrera)
                          ->orWhere('carrera_segunda_opcion_id', $this->filterCarrera);
                })
            )
            ->when($this->filterGestion, fn($q) => $q->where('gestion_id', $this->filterGestion))
            ->when($this->filterEstado,  fn($q) => $q->where('estado_admision', $this->filterEstado))
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
}
