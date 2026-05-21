<?php

namespace App\Livewire\Admin;

use App\Models\Docente;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Docentes extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEditing = false;
    public $docenteId = null;

    // Form fields
    public $name = '';
    public $email = '';
    public $ci = '';
    public $telefono = '';
    public $especialidad = '';
    public $formacion_academica = '';

    protected $rules = [
        'name'               => 'required|string|max:255',
        'email'              => 'required|email|max:255',
        'ci'                 => 'required|string|max:20',
        'telefono'           => 'nullable|string|max:20',
        'especialidad'       => 'nullable|string|max:255',
        'formacion_academica'=> 'nullable|string|max:500',
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
        $this->reset(['docenteId', 'name', 'email', 'ci', 'telefono', 'especialidad', 'formacion_academica']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $docente = Docente::with('user')->findOrFail($id);
        $this->docenteId           = $docente->id;
        $this->name                = $docente->user->name;
        $this->email               = $docente->user->email;
        $this->ci                  = $docente->ci;
        $this->telefono            = $docente->telefono;
        $this->especialidad        = $docente->especialidad;
        $this->formacion_academica = $docente->formacion_academica;
        $this->isEditing           = true;
        $this->showModal           = true;
    }

    public function save()
    {
        $emailRule = $this->isEditing
            ? 'required|email|max:255|unique:users,email,' . Docente::findOrFail($this->docenteId)->user_id
            : 'required|email|max:255|unique:users,email';

        $this->validate(array_merge($this->rules, ['email' => $emailRule]));

        if ($this->isEditing) {
            $docente = Docente::findOrFail($this->docenteId);
            $docente->user->update(['name' => $this->name, 'email' => $this->email]);
            $docente->update([
                'ci'                  => $this->ci,
                'telefono'            => $this->telefono,
                'especialidad'        => $this->especialidad,
                'formacion_academica' => $this->formacion_academica,
            ]);
            session()->flash('message', 'Docente actualizado correctamente.');
        } else {
            $user = User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => bcrypt('password'),
            ]);
            $user->assignRole('Docente');
            Docente::create([
                'user_id'             => $user->id,
                'ci'                  => $this->ci,
                'telefono'            => $this->telefono,
                'especialidad'        => $this->especialidad,
                'formacion_academica' => $this->formacion_academica,
            ]);
            session()->flash('message', 'Docente creado correctamente. Contraseña inicial: password');
        }

        $this->showModal = false;
        $this->reset(['docenteId', 'name', 'email', 'ci', 'telefono', 'especialidad', 'formacion_academica']);
    }

    public function delete($id)
    {
        Docente::findOrFail($id)->delete();
        session()->flash('message', 'Docente eliminado correctamente.');
    }

    public function render()
    {
        $docentes = Docente::query()
            ->with('user')
            ->where(function ($q) {
                $q->whereHas('user', function ($u) {
                    $u->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orWhere('ci', 'like', '%' . $this->search . '%')
                ->orWhere('especialidad', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.docentes', compact('docentes'))
            ->layout('layouts.admin');
    }
}
