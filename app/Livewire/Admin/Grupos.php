<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Grupo;
use App\Models\Materia;
use Livewire\Component;
use Livewire\WithPagination;

class Grupos extends Component
{
    use WithPagination;

    public $search = '';
    public $filterGestion = '';
    public $filterCarrera = '';
    public $filterMateria = '';

    // Static dropdown collections
    public $gestiones = [];
    public $carreras = [];
    public $docentesList = [];

    public $showModal = false;
    public $isEditing = false;
    public $grupoId = null;

    public $nombre = '';
    public $materia_id = '';
    public $gestion_id = '';
    public $cupo_maximo = 40;
    public $docente_id = '';

    protected $rules = [
        'nombre'     => 'required|string|max:255',
        'materia_id' => 'required|exists:materias,id',
        'gestion_id' => 'required|exists:gestiones,id',
        'cupo_maximo' => 'required|integer|min:1',
        'docente_id' => 'nullable|exists:docentes,id',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre del grupo es obligatorio.',
        'materia_id.required' => 'La materia es obligatoria.',
        'materia_id.exists' => 'La materia seleccionada no existe.',
        'gestion_id.required' => 'La gestión es obligatoria.',
        'gestion_id.exists' => 'La gestión seleccionada no existe.',
        'cupo_maximo.required' => 'El cupo máximo es obligatorio.',
        'cupo_maximo.integer' => 'El cupo máximo debe ser un número entero.',
        'cupo_maximo.min' => 'El cupo máximo debe ser al menos 1.',
    ];

    public function mount()
    {
        if (!auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        // Load static dropdowns once
        $this->gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $this->carreras = Carrera::orderBy('nombre')->get();
        $this->docentesList = \App\Models\Docente::orderBy('nombre')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterGestion()
    {
        $this->resetPage();
    }

    public function updatingFilterCarrera()
    {
        $this->resetPage();
    }

    public function updatingFilterMateria()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['grupoId', 'nombre', 'materia_id', 'gestion_id', 'cupo_maximo', 'docente_id']);
        $this->resetValidation();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEdit($id)
    {
        $grupo = Grupo::findOrFail($id);

        $this->grupoId = $grupo->id;
        $this->nombre = $grupo->nombre;
        $this->materia_id = $grupo->materia_id;
        $this->gestion_id = $grupo->gestion_id;
        $this->cupo_maximo = $grupo->cupo_maximo;
        $this->docente_id = $grupo->docentes()->first()?->id ?? '';

        $this->resetValidation();
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $grupo = Grupo::findOrFail($this->grupoId);
            $grupo->update([
                'nombre' => $this->nombre,
                'materia_id' => $this->materia_id,
                'gestion_id' => $this->gestion_id,
                'cupo_maximo' => $this->cupo_maximo,
            ]);
            $grupo->docentes()->sync($this->docente_id ? [$this->docente_id] : []);

            session()->flash('message', 'Grupo actualizado correctamente.');
        } else {
            $grupo = Grupo::create([
                'nombre' => $this->nombre,
                'materia_id' => $this->materia_id,
                'gestion_id' => $this->gestion_id,
                'cupo_maximo' => $this->cupo_maximo,
            ]);
            if ($this->docente_id) {
                $grupo->docentes()->attach($this->docente_id);
            }

            session()->flash('message', 'Grupo creado correctamente.');
        }

        $this->showModal = false;
        $this->reset(['grupoId', 'nombre', 'materia_id', 'gestion_id', 'cupo_maximo', 'docente_id']);
    }

    public function delete($id)
    {
        Grupo::findOrFail($id)->delete();
        session()->flash('message', 'Grupo eliminado correctamente.');
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filterGestion', 'filterCarrera', 'filterMateria']);
        $this->resetPage();
    }

    public function getMateriasProperty()
    {
        return Materia::when($this->filterCarrera, fn($query) => $query->where('carrera_id', $this->filterCarrera))
            ->orderBy('nombre')
            ->get();
    }

    public function render()
    {
        $grupos = Grupo::with(['materia.carrera', 'gestion', 'postulantes', 'docentes'])
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhereHas('materia', fn($q) => $q->where('nombre', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('materia', fn($q) => $q->where('sigla', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('gestion', fn($q) => $q->where('nombre', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterGestion, fn($query) => $query->where('gestion_id', $this->filterGestion))
            ->when($this->filterCarrera, fn($query) => $query->whereHas('materia', fn($q) => $q->where('carrera_id', $this->filterCarrera)))
            ->when($this->filterMateria, fn($query) => $query->where('materia_id', $this->filterMateria))
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.grupos', [
            'grupos' => $grupos,
            'gestiones' => $this->gestiones,
            'carreras' => $this->carreras,
            'materias' => $this->materias,
            'docentesList' => $this->docentesList,
        ])->layout('layouts.admin');
    }
}
