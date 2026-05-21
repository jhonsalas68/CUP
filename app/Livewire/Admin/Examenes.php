<?php

namespace App\Livewire\Admin;

use App\Models\Examen;
use App\Models\Gestion;
use App\Models\Materia;
use Livewire\Component;
use Livewire\WithPagination;

class Examenes extends Component
{
    use WithPagination;

    public $search = '';
    public $filterGestion = '';
    public $filterMateria = '';
    public $showModal = false;
    public $isEditing = false;
    public $examenId = null;

    // Form fields
    public $nombre = '';
    public $materia_id = '';
    public $gestion_id = '';
    public $ponderacion = '';
    public $fecha = '';

    protected $rules = [
        'nombre'      => 'required|string|max:255',
        'materia_id'  => 'required|exists:materias,id',
        'gestion_id'  => 'required|exists:gestiones,id',
        'ponderacion' => 'required|numeric|min:1|max:100',
        'fecha'       => 'required|date',
    ];

    protected $messages = [
        'nombre.required'      => 'El nombre del examen es obligatorio.',
        'materia_id.required'  => 'La materia es obligatoria.',
        'gestion_id.required'  => 'La gestión es obligatoria.',
        'ponderacion.required' => 'La ponderación es obligatoria.',
        'ponderacion.max'      => 'La ponderación no puede superar 100.',
        'fecha.required'       => 'La fecha es obligatoria.',
    ];

    public function mount()
    {
        if (!auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        $gestionActiva = Gestion::where('activo', true)->first();
        if ($gestionActiva) {
            $this->filterGestion = $gestionActiva->id;
            $this->gestion_id    = $gestionActiva->id;
        }
    }

    public function updatingSearch()       { $this->resetPage(); }
    public function updatingFilterGestion(){ $this->resetPage(); }
    public function updatingFilterMateria(){ $this->resetPage(); }

    public function openCreate()
    {
        $this->reset(['examenId', 'nombre', 'materia_id', 'ponderacion', 'fecha']);
        $this->resetValidation();
        $gestionActiva = Gestion::where('activo', true)->first();
        $this->gestion_id = $gestionActiva ? $gestionActiva->id : '';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $examen = Examen::findOrFail($id);
        $this->examenId    = $examen->id;
        $this->nombre      = $examen->nombre;
        $this->materia_id  = $examen->materia_id;
        $this->gestion_id  = $examen->gestion_id;
        $this->ponderacion = $examen->ponderacion;
        $this->fecha       = $examen->fecha ? $examen->fecha->format('Y-m-d') : '';
        $this->isEditing   = true;
        $this->showModal   = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            Examen::findOrFail($this->examenId)->update([
                'nombre'      => $this->nombre,
                'materia_id'  => $this->materia_id,
                'gestion_id'  => $this->gestion_id,
                'ponderacion' => $this->ponderacion,
                'fecha'       => $this->fecha,
            ]);
            session()->flash('message', 'Examen actualizado correctamente.');
        } else {
            Examen::create([
                'nombre'      => $this->nombre,
                'materia_id'  => $this->materia_id,
                'gestion_id'  => $this->gestion_id,
                'ponderacion' => $this->ponderacion,
                'fecha'       => $this->fecha,
            ]);
            session()->flash('message', 'Examen creado correctamente.');
        }

        $this->showModal = false;
        $this->reset(['examenId', 'nombre', 'materia_id', 'ponderacion', 'fecha']);
    }

    public function delete($id)
    {
        Examen::findOrFail($id)->delete();
        session()->flash('message', 'Examen eliminado correctamente.');
    }

    public function render()
    {
        $gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();
        $materias  = Materia::with('carrera')->orderBy('nombre')->get();

        $examenes = Examen::query()
            ->with(['materia.carrera', 'gestion'])
            ->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterGestion, fn($q) => $q->where('gestion_id', $this->filterGestion))
            ->when($this->filterMateria, fn($q) => $q->where('materia_id', $this->filterMateria))
            ->orderBy('fecha', 'desc')
            ->paginate(15);

        return view('livewire.admin.examenes', compact('examenes', 'gestiones', 'materias'))
            ->layout('layouts.admin');
    }
}
