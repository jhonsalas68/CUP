<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Gestion;
use Livewire\Component;
use Livewire\WithPagination;

class Carreras extends Component
{
    use WithPagination;

    public $search = '';

    public $showModal = false;

    public $isEditing = false;

    public $carreraId = null;

    // Static dropdown collections
    public $gestiones = [];

    public $carrerasList = [];

    // Form fields
    public $nombre = '';

    public $sigla = '';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'sigla' => 'required|string|max:20',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'sigla.required' => 'La sigla es obligatoria.',
    ];

    public function mount()
    {
        if (! auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
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
        $this->reset(['carreraId', 'nombre', 'sigla']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $carrera = Carrera::findOrFail($id);
        $this->carreraId = $carrera->id;
        $this->nombre = $carrera->nombre;
        $this->sigla = $carrera->sigla;
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

    public function limpiarFiltros()
    {
        $this->reset(['search']);
        $this->resetPage();
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

        $feedback = [];

        if (preg_match('/(?:buscar|busca|nombre|sigla)\s+([a-záéíóúñ0-9\s\-]+)/', $transcript, $matches)) {
            $this->search = trim($matches[1]);
            $feedback[] = 'Buscar: "'.$this->search.'"';
        } else {
            $this->search = trim($transcript);
            $feedback[] = 'Buscar: "'.$this->search.'"';
        }

        session()->flash('voice_feedback', 'Filtros aplicados: '.implode(', ', $feedback));
        $this->resetPage();
    }

    public function render()
    {
        $carreras = Carrera::query()
            ->where(function ($q) {
                $q->where('nombre', 'like', '%'.$this->search.'%')
                    ->orWhere('sigla', 'like', '%'.$this->search.'%');
            })
            ->withCount('materias')
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.carreras', [
            'carreras' => $carreras,
            'gestiones' => $this->gestiones,
            'carrerasList' => $this->carrerasList,
        ])->layout('layouts.admin');
    }
}
