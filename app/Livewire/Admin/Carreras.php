<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use Livewire\Component;
use Livewire\WithPagination;

class Carreras extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEditing = false;
    public $carreraId = null;

    // Form fields
    public $nombre = '';
    public $sigla = '';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'sigla'  => 'required|string|max:20',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'sigla.required'  => 'La sigla es obligatoria.',
    ];

    public function mount()
    {
        if (!auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['carreraId', 'nombre', 'sigla']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $carrera = Carrera::findOrFail($id);
        $this->carreraId = $carrera->id;
        $this->nombre    = $carrera->nombre;
        $this->sigla     = $carrera->sigla;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $carrera = Carrera::findOrFail($this->carreraId);
            $carrera->update(['nombre' => $this->nombre, 'sigla' => $this->sigla]);
            session()->flash('message', 'Carrera actualizada correctamente.');
        } else {
            Carrera::create(['nombre' => $this->nombre, 'sigla' => $this->sigla]);
            session()->flash('message', 'Carrera creada correctamente.');
        }

        $this->showModal = false;
        $this->reset(['carreraId', 'nombre', 'sigla']);
    }

    public function delete($id)
    {
        Carrera::findOrFail($id)->delete();
        session()->flash('message', 'Carrera eliminada correctamente.');
    }

    public function render()
    {
        $carreras = Carrera::query()
            ->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('sigla', 'like', '%' . $this->search . '%');
            })
            ->withCount('materias')
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.carreras', compact('carreras'))
            ->layout('layouts.admin');
    }
}
