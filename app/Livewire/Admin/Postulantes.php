<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Gestion;
use App\Models\Postulante;
use Livewire\Component;
use Livewire\WithPagination;

class Postulantes extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCarrera = '';
    public $filterGestion = '';
    public $filterEstado = '';

    public $estadosOptions = [
        'pendiente'                => 'Pendiente',
        'admitido_primera_opcion'  => 'Admitido (1ra opción)',
        'admitido_segunda_opcion'  => 'Admitido (2da opción)',
        'reprobado'                => 'Reprobado',
        'no_presentado'            => 'No presentado',
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

    public function cambiarEstado($id, $estado)
    {
        Postulante::findOrFail($id)->update(['estado_admision' => $estado]);
        session()->flash('message', 'Estado de postulante actualizado.');
    }

    public function render()
    {
        $carreras = Carrera::orderBy('nombre')->get();
        $gestiones = Gestion::orderBy('fecha_inicio', 'desc')->get();

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
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.admin.postulantes', compact('postulantes', 'carreras', 'gestiones'))
            ->layout('layouts.admin');
    }
}
