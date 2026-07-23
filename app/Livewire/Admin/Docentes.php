<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Docente;
use App\Models\Gestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Docentes extends Component
{
    use WithPagination;

    public $search = '';

    public $showModal = false;

    public $isEditing = false;

    public $docenteId = null;

    // Static dropdown collections
    public $gestiones = [];

    public $carrerasList = [];

    // Form fields
    public $name = '';

    public $email = '';

    public $ci = '';

    public $telefono = '';

    public $especialidad = '';

    public $formacion_academica = '';

    public $profesional_area = true;

    public $tiene_maestria = true;

    public $tiene_diplomado = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'ci' => 'required|string|max:20',
        'telefono' => 'nullable|string|max:20',
        'especialidad' => 'nullable|string|max:255',
        'formacion_academica' => 'nullable|string|max:500',
        'profesional_area' => 'boolean',
        'tiene_maestria' => 'boolean',
        'tiene_diplomado' => 'boolean',
    ];

    public function mount()
    {
        if (! auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No authorized.');
        }

        // Load static dropdowns once
        $this->gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $this->carrerasList = Carrera::orderBy('nombre')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['docenteId', 'name', 'email', 'ci', 'telefono', 'especialidad', 'formacion_academica', 'profesional_area', 'tiene_maestria', 'tiene_diplomado']);
        $this->profesional_area = true;
        $this->tiene_maestria = true;
        $this->tiene_diplomado = true;
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $docente = Docente::with('user')->findOrFail($id);
        $this->docenteId = $docente->id;
        $this->name = $docente->user->name;
        $this->email = $docente->user->email;
        $this->ci = $docente->ci;
        $this->telefono = $docente->telefono;
        $this->especialidad = $docente->especialidad;
        $this->formacion_academica = $docente->formacion_academica;
        $this->profesional_area = (bool) $docente->profesional_area;
        $this->tiene_maestria = (bool) $docente->tiene_maestria;
        $this->tiene_diplomado = (bool) $docente->tiene_diplomado;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $emailRule = $this->isEditing
            ? 'required|email|max:255|unique:users,email,'.Docente::findOrFail($this->docenteId)->user_id
            : 'required|email|max:255|unique:users,email';

        $this->validate(array_merge($this->rules, ['email' => $emailRule]));

        if ($this->isEditing) {
            $docente = Docente::findOrFail($this->docenteId);
            $docente->user->update(['name' => $this->name, 'email' => $this->email]);
            $docente->update([
                'nombre' => $this->name,
                'ci' => $this->ci,
                'telefono' => $this->telefono,
                'especialidad' => $this->especialidad,
                'formacion_academica' => $this->formacion_academica,
                'profesional_area' => (bool) $this->profesional_area,
                'tiene_maestria' => (bool) $this->tiene_maestria,
                'tiene_diplomado' => (bool) $this->tiene_diplomado,
            ]);
            session()->flash('message', 'Docente actualizado correctamente.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => bcrypt('password'),
            ]);
            $user->assignRole('Docente');
            Docente::create([
                'user_id' => $user->id,
                'nombre' => $this->name,
                'ci' => $this->ci,
                'telefono' => $this->telefono,
                'especialidad' => $this->especialidad,
                'formacion_academica' => $this->formacion_academica,
                'profesional_area' => (bool) $this->profesional_area,
                'tiene_maestria' => (bool) $this->tiene_maestria,
                'tiene_diplomado' => (bool) $this->tiene_diplomado,
            ]);
            session()->flash('message', 'Docente creado correctamente. Contraseña inicial: password');
        }

        $this->showModal = false;
        $this->reset(['docenteId', 'name', 'email', 'ci', 'telefono', 'especialidad', 'formacion_academica', 'profesional_area', 'tiene_maestria', 'tiene_diplomado']);
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $docente = Docente::findOrFail($id);
            if ($docente->user) {
                $docente->user->delete();
            }
            $docente->delete();
        });
        session()->flash('message', 'Docente y su usuario asociado eliminados correctamente.');
    }

    public function limpiarFiltros()
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    private function removeAccents($str)
    {
        $unwanted_array = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ];

        return strtr($str, $unwanted_array);
    }

    public function processVoiceCommand($transcript)
    {
        $transcript = mb_strtolower($transcript, 'UTF-8');

        if (str_contains($transcript, 'limpiar') || str_contains($transcript, 'restablecer') || str_contains($transcript, 'todos') || str_contains($transcript, 'reiniciar') || str_contains($transcript, 'quitar')) {
            $this->reset(['search']);
            session()->flash('voice_feedback', 'Filtros restablecidos.');
            $this->resetPage();

            return;
        }

        // Clean up common voice command prefixes
        $cleaned = $transcript;

        // 1. Strip "mostrar/buscar/filtrar docentes con especialidad en/de/etc"
        $cleaned = preg_replace('/^(?:mostrar|buscar|busca|filtrar|ver)?\s*(?:a\s+)?(?:los\s+|el\s+)?docentes?\s+(?:con\s+especialidad\s+en|con\s+especialidad\s+de|de\s+la\s+especialidad\s+en|de\s+la\s+especialidad\s+de|de\s+especialidad|de\s+|en\s+)/u', '', $cleaned);

        // 2. Strip "filtrar por especialidad en/de", "especialidad en/de", "filtrar por"
        $cleaned = preg_replace('/^(?:filtrar\s+por\s+)?especialidad\s+(?:en\s+|de\s+)?/u', '', $cleaned);
        $cleaned = preg_replace('/^filtrar\s+por\s+/u', '', $cleaned);

        // 3. Strip "buscar/busca/ver/mostrar"
        $cleaned = preg_replace('/^(?:buscar|busca|ver|mostrar)\s+(?:a\s+|los\s+|el\s+)?/u', '', $cleaned);

        $cleaned = trim($cleaned);

        if ($cleaned !== '') {
            $this->search = $cleaned;
            session()->flash('voice_feedback', 'Filtros aplicados: Buscar "'.$this->search.'"');
        } else {
            $this->search = trim($transcript);
            session()->flash('voice_feedback', 'Filtros aplicados: Buscar "'.$this->search.'"');
        }

        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);

        $docentes = Docente::query()
            ->with('user')
            ->when($search, function ($query) use ($search) {
                $driver = DB::connection()->getDriverName();
                if ($driver === 'pgsql') {
                    $normalizedSearch = $this->removeAccents(mb_strtolower($search, 'UTF-8'));

                    $query->where(function ($q) use ($normalizedSearch) {
                        $q->whereHas('user', function ($u) use ($normalizedSearch) {
                            $u->whereRaw("translate(lower(name), 'áéíóúüñ', 'aeiuuun') LIKE ?", ['%'.$normalizedSearch.'%'])
                                ->orWhereRaw("translate(lower(email), 'áéíóúüñ', 'aeiuuun') LIKE ?", ['%'.$normalizedSearch.'%']);
                        })
                            ->orWhereRaw("translate(lower(nombre), 'áéíóúüñ', 'aeiuuun') LIKE ?", ['%'.$normalizedSearch.'%'])
                            ->orWhereRaw("translate(lower(ci), 'áéíóúüñ', 'aeiuuun') LIKE ?", ['%'.$normalizedSearch.'%'])
                            ->orWhereRaw("translate(lower(especialidad), 'áéíóúüñ', 'aeiuuun') LIKE ?", ['%'.$normalizedSearch.'%']);
                    });
                } else {
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        })
                            ->orWhere('nombre', 'like', '%'.$search.'%')
                            ->orWhere('ci', 'like', '%'.$search.'%')
                            ->orWhere('especialidad', 'like', '%'.$search.'%');
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.docentes', [
            'docentes' => $docentes,
            'gestiones' => $this->gestiones,
            'carrerasList' => $this->carrerasList,
        ])->layout('layouts.admin');
    }
}
