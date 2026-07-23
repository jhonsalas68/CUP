<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Postulante;
use App\Models\Cupo;
use App\Models\Carrera;
use App\Models\Nota;
use Illuminate\Support\Facades\DB;

$val = Postulante::where('ci', '1300994')->first();

if (!$val) {
    echo "Valentina not found!\n";
    exit;
}

echo "Postulante: {$val->nombres_apellidos} (CI: {$val->ci})\n";
echo "Nota Final: {$val->nota_final}\n";
echo "Estado Admisión: {$val->estado_admision}\n";
echo "1ra Opción: " . ($val->carreraPrimeraOpn?->nombre ?? 'N/A') . " (Sigla: " . ($val->carreraPrimeraOpn?->sigla ?? 'N/A') . ")\n";
echo "2da Opción: " . ($val->carreraSegundaOpn?->nombre ?? 'N/A') . " (Sigla: " . ($val->carreraSegundaOpn?->sigla ?? 'N/A') . ")\n";

// Check if she failed any individual exam/materia
echo "\nNotas por Examen:\n";
$notas = Nota::where('postulante_id', $val->id)
    ->with('examen.materia')
    ->get();
foreach ($notas as $n) {
    echo "- Materia: {$n->examen->materia->sigla} | Examen: {$n->examen->nombre} (Ponderación: {$n->examen->ponderacion}%) | Puntaje: {$n->puntaje}\n";
}

// Check ranking of SIS first option
$sis = Carrera::where('sigla', 'SIS')->first();
$sisCupo = Cupo::where('carrera_id', $sis->id)->where('gestion_id', $val->gestion_id)->first();
echo "\nCupo SIS: 1ra Opción = {$sisCupo->cantidad_primera_opcion} | 2da Opción = {$sisCupo->cantidad_segunda_opcion}\n";

// Count how many SIS 1ra opción candidates have higher or equal grades
$betterSis = Postulante::where('gestion_id', $val->gestion_id)
    ->where('carrera_primera_opcion_id', $sis->id)
    ->where('estado_admision', '!=', 'reprobado')
    ->orderByDesc('nota_final')
    ->get();

$rankSis = 0;
foreach ($betterSis as $idx => $p) {
    if ($p->id === $val->id) {
        $rankSis = $idx + 1;
        break;
    }
}
echo "Ranking de Valentina en 1ra opción (SIS): #{$rankSis} de " . $betterSis->count() . " candidatos aprobados.\n";

// If rank > cupo, she overflows to 2nd option. Check INF
$inf = Carrera::where('sigla', 'INF')->first();
$infCupo = Cupo::where('carrera_id', $inf->id)->where('gestion_id', $val->gestion_id)->first();
echo "\nCupo INF: 1ra Opción = {$infCupo->cantidad_primera_opcion} | 2da Opción = {$infCupo->cantidad_segunda_opcion}\n";

// Who got admitted to INF 2da opción?
$admitidosInfSegunda = Postulante::where('gestion_id', $val->gestion_id)
    ->where('carrera_segunda_opcion_id', $inf->id)
    ->where('estado_admision', 'admitido_segunda_opcion')
    ->orderByDesc('nota_final')
    ->get();

echo "Total admitidos en INF 2da Opción: " . $admitidosInfSegunda->count() . "\n";
echo "Notas de los admitidos en INF 2da Opción:\n";
foreach ($admitidosInfSegunda as $idx => $p) {
    echo "#" . ($idx+1) . ": CI: {$p->ci} | {$p->nombres_apellidos} | Nota: {$p->nota_final}\n";
}

// Find Valentina's ranking among all candidates whose 2nd option is INF and didn't get their 1st option
$candidatesInf2nd = Postulante::where('gestion_id', $val->gestion_id)
    ->where('carrera_segunda_opcion_id', $inf->id)
    ->whereNotIn('estado_admision', ['reprobado'])
    ->orderByDesc('nota_final')
    ->get();

echo "\nRanking general de postulantes con 2da Opción INF (que no entraron a su 1ra opción y están aprobados):\n";
$rankInf2 = 1;
foreach ($candidatesInf2nd as $p) {
    // Did they fail their first option?
    $firstOption = Carrera::find($p->carrera_primera_opcion_id);
    $firstOptionCupo = Cupo::where('carrera_id', $firstOption->id)->where('gestion_id', $val->gestion_id)->first();
    // Count better candidates for their first option
    $betterFirst = Postulante::where('gestion_id', $val->gestion_id)
        ->where('carrera_primera_opcion_id', $firstOption->id)
        ->where('nota_final', '>', $p->nota_final)
        ->count();
    $failedFirst = ($betterFirst >= $firstOptionCupo->cantidad_primera_opcion);

    if ($failedFirst) {
        echo "Candidate: {$p->nombres_apellidos} | Nota: {$p->nota_final} | Estado Actual: {$p->estado_admision}";
        if ($p->id === $val->id) {
            echo " <-- VALENTINA (#{$rankInf2})";
        }
        echo "\n";
        $rankInf2++;
    }
}
