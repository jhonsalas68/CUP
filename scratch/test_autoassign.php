<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Grupo;
use App\Models\Postulante;

echo "Testing auto-assign on Postgres...\n";
try {
    $grupo = Grupo::with('postulantes')->first();
    if (!$grupo) {
        echo "No groups found.\n";
        exit;
    }
    echo "Using Group: " . $grupo->nombre . " (Materia: " . $grupo->materia->nombre . ")\n";

    $unassigned = [];
    foreach ($grupo->postulantes as $postulante) {
        if ($postulante->pivot->nro_asiento === null) {
            $unassigned[] = $postulante;
        }
    }

    echo "Total unassigned students: " . count($unassigned) . "\n";
    if (count($unassigned) === 0) {
        // Reset some seats first to test
        echo "Seeding some null seats to test...\n";
        foreach ($grupo->postulantes->take(5) as $postulante) {
            $grupo->postulantes()->updateExistingPivot($postulante->id, ['nro_asiento' => null]);
        }
        // reload
        $grupo = Grupo::with('postulantes')->first();
        $unassigned = [];
        foreach ($grupo->postulantes as $postulante) {
            if ($postulante->pivot->nro_asiento === null) {
                $unassigned[] = $postulante;
            }
        }
        echo "Now unassigned: " . count($unassigned) . "\n";
    }

    // Try updating one
    $student = $unassigned[0];
    echo "Updating student " . $student->nombres_apellidos . " (ID: " . $student->id . ") to seat 1...\n";
    $result = $grupo->postulantes()->updateExistingPivot($student->id, ['nro_asiento' => 1]);
    echo "Result (affected rows): " . ($result !== false ? "SUCCESS ($result)" : "FAILED") . "\n";

    // Verify
    $verify = $grupo->fresh()->postulantes()->find($student->id);
    echo "Verified seat: " . ($verify->pivot->nro_asiento ?? 'NULL') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
