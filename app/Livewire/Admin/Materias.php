<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Materia;
use Livewire\Component;
use Livewire\WithPagination;

class Materias extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCarrera = '';
    public $showModal = false;
    public $isEditing = false;
    public $materiaId = null;

    // Form fields
    public $nombre = '';
    public $sigla = '';
    public $carrera_id = '';

    protected $rules = [
        'nombre'     => 'required|string|max:255',
        'sigla'      => 'required|string|max:20',
        'carrera_id' => 'required|exists:carreras,id',
    ];

    protected $messages = [
        'nombre.required'     => 'El nombre es obligatorio.',
        'sigla.required'      => 'La sigla es obligatoria.',
        'carrera_id.required' => 'La carrera es obligatoria.',
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

    public function updatingFilterCarrera()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['materiaId', 'nombre', 'sigla', 'carrera_id']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $materia = Materia::findOrFail($id);
        $this->materiaId  = $materia->id;
        $this->nombre     = $materia->nombre;
        $this->sigla      = $materia->sigla;
        $this->carrera_id = $materia->carrera_id;
        $this->isEditing  = true;
        $this->showModal  = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $materia = Materia::findOrFail($this->materiaId);
            $materia->update([
                'nombre'     => $this->nombre,
                'sigla'      => $this->sigla,
                'carrera_id' => $this->carrera_id,
            ]);
            session()->flash('message', 'Materia actualizada correctamente.');
        } else {
            Materia::create([
                'nombre'     => $this->nombre,
                'sigla'      => $this->sigla,
                'carrera_id' => $this->carrera_id,
            ]);
            session()->flash('message', 'Materia creada correctamente.');
        }

        $this->showModal = false;
        $this->reset(['materiaId', 'nombre', 'sigla', 'carrera_id']);
    }

    public function delete($id)
    {
        Materia::findOrFail($id)->delete();
        session()->flash('message', 'Materia eliminada correctamente.');
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filterCarrera']);
        $this->resetPage();
    }

    public function processVoiceCommand($transcript)
    {
        $transcript = mb_strtolower($transcript, 'UTF-8');
        
        if (str_contains($transcript, 'limpiar') || str_contains($transcript, 'restablecer') || str_contains($transcript, 'todos') || str_contains($transcript, 'reiniciar') || str_contains($transcript, 'quitar')) {
            $this->reset(['search', 'filterCarrera']);
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

        // Búsqueda general
        if (preg_match('/(?:buscar|busca|nombre|materia)\s+([a-záéíóúñ0-9\s\-]+)/', $transcript, $matches)) {
            $this->search = trim($matches[1]);
            $feedback[] = 'Buscar: "' . $this->search . '"';
        }

        if (empty($feedback)) {
            $this->search = trim($transcript);
            $feedback[] = 'Buscar: "' . $this->search . '"';
        }

        session()->flash('voice_feedback', 'Filtros aplicados: ' . implode(', ', $feedback));
        $this->resetPage();
    }

    public function render()
    {
        $carreras = Carrera::orderBy('nombre')->get();
        $gestiones = \App\Models\Gestion::orderBy('fecha_inicio', 'desc')->get();
        $carrerasList = $carreras;

        $materias = Materia::query()
            ->with(['carrera', 'grupos.docentes', 'grupos.postulantes'])
            ->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('sigla', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterCarrera, fn($q) => $q->where('carrera_id', $this->filterCarrera))
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.materias', compact('materias', 'carreras', 'gestiones', 'carrerasList'))
            ->layout('layouts.admin');
    }
}
