<?php

namespace App\Livewire\Admin;

use App\Models\Aula;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Aulas extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEditing = false;
    public $aulaId = null;

    // Form fields
    public $nombre = '';
    public $capacidad = '';
    public $ubicacion = '';

    protected function rules()
    {
        return [
            'nombre'    => [
                'required',
                'string',
                'max:255',
                Rule::unique('aulas', 'nombre')->ignore($this->aulaId),
            ],
            'capacidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'nombre.required'    => 'El nombre del aula es obligatorio.',
        'nombre.unique'      => 'Ya existe un aula con este nombre.',
        'capacidad.required' => 'La capacidad es obligatoria.',
        'capacidad.integer'  => 'La capacidad debe ser un número entero.',
        'capacidad.min'      => 'La capacidad debe ser de al menos 1 persona.',
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
        $this->reset(['aulaId', 'nombre', 'capacidad', 'ubicacion']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $aula = Aula::findOrFail($id);
        $this->aulaId = $aula->id;
        $this->nombre = $aula->nombre;
        $this->capacidad = $aula->capacidad;
        $this->ubicacion = $aula->ubicacion;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $aula = Aula::findOrFail($this->aulaId);
            $aula->update([
                'nombre'    => $this->nombre,
                'capacidad' => $this->capacidad,
                'ubicacion' => $this->ubicacion,
            ]);
            session()->flash('message', 'Aula actualizada correctamente.');
        } else {
            Aula::create([
                'nombre'    => $this->nombre,
                'capacidad' => $this->capacidad,
                'ubicacion' => $this->ubicacion,
            ]);
            session()->flash('message', 'Aula creada correctamente.');
        }

        $this->showModal = false;
        $this->reset(['aulaId', 'nombre', 'capacidad', 'ubicacion']);
    }

    public function delete($id)
    {
        Aula::findOrFail($id)->delete();
        session()->flash('message', 'Aula eliminada correctamente.');
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

        if (preg_match('/(?:buscar|busca|aula|nombre|ubicación|ubicacion)\s+([a-záéíóúñ0-9\s\-]+)/', $transcript, $matches)) {
            $this->search = trim($matches[1]);
            $feedback[] = 'Buscar: "' . $this->search . '"';
        } else {
            $this->search = trim($transcript);
            $feedback[] = 'Buscar: "' . $this->search . '"';
        }

        session()->flash('voice_feedback', 'Filtros aplicados: ' . implode(', ', $feedback));
        $this->resetPage();
    }

    public function render()
    {
        $aulas = Aula::query()
            ->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('ubicacion', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.aulas', compact('aulas'))
            ->layout('layouts.admin');
    }
}
