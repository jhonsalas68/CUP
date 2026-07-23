<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Postulante;
use App\Models\Carrera;
use App\Models\Cupo;
use App\Models\Gestion;
use App\Services\AdmissionSelectionService;

$gestion = Gestion::where('activo', true)->first();
if (!$gestion) {
    echo "No active gestion found.\n";
    exit;
}

echo "Active Gestion: {$gestion->nombre} (ID: {$gestion->id})\n\n";

$carreras = Carrera::all();
$service = new AdmissionSelectionService();

foreach ($carreras as $carrera) {
    $total = Postulante::where('gestion_id', $gestion->id)
        ->where('carrera_primera_opcion_id', $carrera->id)
        ->count();

    $cupo = Cupo::where('carrera_id', $carrera->id)
        ->where('gestion_id', $gestion->id)
        ->first();

    $cupo1 = $cupo ? $cupo->cantidad_primera_opcion : 0;
    $cupo2 = $cupo ? $cupo->cantidad_segunda_opcion : 0;

    // Count approved
    $postulantes = Postulante::where('gestion_id', $gestion->id)
        ->where('carrera_primera_opcion_id', $carrera->id)
        ->get();

    $approvedCount = 0;
    foreach ($postulantes as $p) {
        $eval = $service->evaluatePostulante($p, $gestion->id);
        if ($eval['aprobado_academico']) {
            $approvedCount++;
        }
    }

    echo "Carrera: {$carrera->nombre} ({$carrera->sigla})\n";
    echo "  Total Postulantes (1ra Opcion): {$total}\n";
    echo "  Aprobados Academicos: {$approvedCount}\n";
    echo "  Cupo 1ra Opcion: {$cupo1}\n";
    echo "  Cupo 2da Opcion: {$cupo2}\n";
    echo "----------------------------------------\n";
}
