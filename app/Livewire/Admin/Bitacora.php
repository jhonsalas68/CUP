<?php

namespace App\Livewire\Admin;

use App\Models\Bitacora as BitacoraModel;
use Livewire\Component;
use Livewire\WithPagination;

class Bitacora extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedAction = '';
    public $selectedLog = null;
    public $showDetailModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedAction' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedAction()
    {
        $this->resetPage();
    }

    public function mount()
    {
        if (!auth()->user()->hasRole('Administrador')) {
            abort(403, 'No autorizado.');
        }
    }

    public function showDetail($logId)
    {
        $this->selectedLog = BitacoraModel::with('user')->findOrFail($logId);
        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedLog = null;
    }

    public function clearLogs()
    {
        if (!auth()->user()->hasRole('Administrador')) {
            abort(403);
        }
        
        BitacoraModel::truncate();
        session()->flash('message', 'Bitácora de actividades vaciada correctamente.');
        $this->resetPage();
    }

    public function render()
    {
        if (!auth()->user()->hasRole('Administrador')) {
            abort(403, 'No autorizado.');
        }

        $query = BitacoraModel::with('user')
            ->latest('id');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('descripcion', 'like', '%' . $this->search . '%')
                  ->orWhere('objeto', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($uq) {
                      $uq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->selectedAction) {
            $query->where('action', $this->selectedAction);
        }

        $logs = $query->paginate(15);
        $actions = ['crear', 'actualizar', 'eliminar', 'proceso_admision', 'login', 'logout'];

        return view('livewire.admin.bitacora', [
            'logs' => $logs,
            'actions' => $actions
        ])->layout('layouts.admin');
    }
}
