<?php

use App\Models\Docente;
use App\Models\Postulante;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate Docentes
        Docente::whereNull('nombre')->chunkById(100, function ($docentes) {
            foreach ($docentes as $docente) {
                if ($docente->user) {
                    $docente->update(['nombre' => $docente->user->name]);
                }
            }
        });

        // Populate Postulantes
        Postulante::whereNull('nombres_apellidos')->chunkById(100, function ($postulantes) {
            foreach ($postulantes as $postulante) {
                if ($postulante->user) {
                    $postulante->update(['nombres_apellidos' => $postulante->user->name]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed as we are just backfilling data
    }
};
