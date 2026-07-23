<?php

namespace App\Livewire\Admin;

use App\Models\Aula;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VisualizadorAula extends Component
{
    public $aula;

    public $grupos = [];

    public $selectedGrupoId = null;

    public $distributionCriteria = 'alfabetico_asc';

    public $cols = 6;

    public $rows = 5;

    // We will pass these arrays to the view
    public $seatingMap = [];

    public $unassignedStudents = [];

    public function mount($aulaId)
    {
        if (! auth()->user()->hasAnyRole(['Administrador', 'Coordinador'])) {
            abort(403, 'No autorizado.');
        }

        $this->aula = Aula::findOrFail($aulaId);

        // Load groups assigned to this classroom via their schedules (horarios)
        $this->grupos = Grupo::whereHas('horarios', function ($query) {
            $query->where('aula_id', $this->aula->id);
        })->with('materia')->get();

        if ($this->grupos->isNotEmpty()) {
            $this->selectedGrupoId = $this->grupos->first()->id;
        }

        $this->cargarDatos();
    }

    public function updatedSelectedGrupoId()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        if (! $this->selectedGrupoId) {
            $this->seatingMap = [];
            $this->unassignedStudents = [];

            return;
        }

        $grupo = Grupo::with(['postulantes'])->findOrFail($this->selectedGrupoId);

        // Grid calculation: standard 6 columns (very readable)
        $this->cols = 6;
        $this->rows = (int) ceil($this->aula->capacidad / $this->cols);

        // Initialize empty seating map
        $this->seatingMap = [];
        for ($i = 1; $i <= $this->aula->capacidad; $i++) {
            $this->seatingMap[$i] = null;
        }

        $this->unassignedStudents = [];

        foreach ($grupo->postulantes as $postulante) {
            $seat = $postulante->pivot->nro_asiento;
            if ($seat && $seat >= 1 && $seat <= $this->aula->capacidad) {
                $this->seatingMap[$seat] = [
                    'id' => $postulante->id,
                    'nombres_apellidos' => $postulante->nombres_apellidos,
                    'ci' => $postulante->ci,
                    'estado_admision' => $postulante->estado_admision,
                    'nota_final' => $postulante->nota_final,
                    'iniciales' => $this->getIniciales($postulante->nombres_apellidos),
                ];
            } else {
                $this->unassignedStudents[] = [
                    'id' => $postulante->id,
                    'nombres_apellidos' => $postulante->nombres_apellidos,
                    'ci' => $postulante->ci,
                    'estado_admision' => $postulante->estado_admision,
                    'nota_final' => $postulante->nota_final,
                ];
            }
        }
    }

    private function getIniciales($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        $count = 0;
        foreach ($words as $w) {
            if ($w && $count < 2) {
                $initials .= strtoupper(substr($w, 0, 1));
                $count++;
            }
        }

        return $initials ?: 'P';
    }

    public function assignSeat($studentId, $seatNumber)
    {
        if (! $this->selectedGrupoId) {
            return;
        }

        $seatNumber = (int) $seatNumber;
        if ($seatNumber < 1 || $seatNumber > $this->aula->capacidad) {
            return;
        }

        $grupo = Grupo::with(['postulantes'])->findOrFail($this->selectedGrupoId);

        // Check if the seat is occupied
        $occupiedBy = $grupo->postulantes()
            ->wherePivot('nro_asiento', $seatNumber)
            ->first();

        // Check if the dragged student was already seated somewhere else
        $draggedStudent = $grupo->postulantes()->find($studentId);
        if (! $draggedStudent) {
            return;
        }
        $oldSeat = $draggedStudent->pivot->nro_asiento;

        if ($occupiedBy) {
            if ($oldSeat) {
                // Swap seats
                $grupo->postulantes()->updateExistingPivot($draggedStudent->id, ['nro_asiento' => $seatNumber]);
                $grupo->postulantes()->updateExistingPivot($occupiedBy->id, ['nro_asiento' => $oldSeat]);
            } else {
                // Move occupied student to unassigned, and assign dragged student to this seat
                $grupo->postulantes()->updateExistingPivot($occupiedBy->id, ['nro_asiento' => null]);
                $grupo->postulantes()->updateExistingPivot($draggedStudent->id, ['nro_asiento' => $seatNumber]);
            }
        } else {
            // Simple move
            $grupo->postulantes()->updateExistingPivot($draggedStudent->id, ['nro_asiento' => $seatNumber]);
        }

        $this->cargarDatos();
        session()->flash('message', 'Asiento asignado correctamente.');
    }

    public function unassignSeat($studentId)
    {
        if (! $this->selectedGrupoId) {
            return;
        }

        $grupo = Grupo::findOrFail($this->selectedGrupoId);
        $grupo->postulantes()->updateExistingPivot($studentId, ['nro_asiento' => null]);
        $this->cargarDatos();
        session()->flash('message', 'Asiento desasignado.');
    }

    public function autoAssignRemaining()
    {
        if (! $this->selectedGrupoId) {
            return;
        }

        $grupo = Grupo::with(['postulantes'])->findOrFail($this->selectedGrupoId);

        $unassigned = [];
        $occupiedSeats = [];

        foreach ($grupo->postulantes as $postulante) {
            $seat = $postulante->pivot->nro_asiento;
            if ($seat && $seat >= 1 && $seat <= $this->aula->capacidad) {
                $occupiedSeats[$seat] = $postulante->id;
            } else {
                $unassigned[] = $postulante;
            }
        }

        if (empty($unassigned)) {
            session()->flash('message', 'No hay estudiantes pendientes de asignación.');

            return;
        }

        // Sort unassigned students based on selected criteria
        switch ($this->distributionCriteria) {
            case 'alfabetico_desc':
                usort($unassigned, function ($a, $b) {
                    return strcmp($b->nombres_apellidos, $a->nombres_apellidos);
                });
                break;
            case 'nota_desc':
                usort($unassigned, function ($a, $b) {
                    $gradeA = $a->nota_final ?? 0;
                    $gradeB = $b->nota_final ?? 0;

                    return $gradeB <=> $gradeA; // Descending
                });
                break;
            case 'nota_asc':
                usort($unassigned, function ($a, $b) {
                    $gradeA = $a->nota_final ?? 0;
                    $gradeB = $b->nota_final ?? 0;

                    return $gradeA <=> $gradeB; // Ascending
                });
                break;
            case 'aleatorio':
                shuffle($unassigned);
                break;
            case 'alfabetico_asc':
            default:
                usort($unassigned, function ($a, $b) {
                    return strcmp($a->nombres_apellidos, $b->nombres_apellidos);
                });
                break;
        }

        DB::transaction(function () use ($grupo, $unassigned, $occupiedSeats) {
            $unassignedIndex = 0;
            $totalUnassigned = count($unassigned);

            for ($i = 1; $i <= $this->aula->capacidad; $i++) {
                if ($unassignedIndex >= $totalUnassigned) {
                    break;
                }

                if (! isset($occupiedSeats[$i])) {
                    $student = $unassigned[$unassignedIndex];

                    DB::table('postulante_grupo')
                        ->where('grupo_id', $grupo->id)
                        ->where('postulante_id', $student->id)
                        ->update(['nro_asiento' => $i]);

                    $unassignedIndex++;
                }
            }
        });

        $this->cargarDatos();
        session()->flash('message', 'Distribución automática completada.');
    }

    public function clearAllAssignments()
    {
        if (! $this->selectedGrupoId) {
            return;
        }

        $grupo = Grupo::findOrFail($this->selectedGrupoId);

        DB::table('postulante_grupo')
            ->where('grupo_id', $grupo->id)
            ->whereNotNull('nro_asiento')
            ->update(['nro_asiento' => null]);

        $this->cargarDatos();
        session()->flash('message', 'Se han liberado todos los asientos.');
    }

    public function render()
    {
        $grupo = $this->selectedGrupoId
            ? Grupo::find($this->selectedGrupoId)
            : null;

        return view('livewire.admin.visualizador-aula', [
            'grupo' => $grupo,
        ])->layout('layouts.admin', ['title' => 'Visualizador de Aula 2D']);
    }
}
