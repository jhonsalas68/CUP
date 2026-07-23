<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$counts = DB::table('postulantes')
    ->select('estado_admision', DB::raw('count(*) as total'))
    ->groupBy('estado_admision')
    ->get();

echo "Current DB state for postulantes:\n";
foreach ($counts as $c) {
    echo "Status: {$c->estado_admision} -> Total: {$c->total}\n";
}

$cupos = DB::table('cupos')
    ->join('carreras', 'cupos.carrera_id', '=', 'carreras.id')
    ->select('carreras.sigla', 'cupos.cantidad_primera_opcion', 'cupos.cantidad_segunda_opcion')
    ->get();

echo "\nCupos configured:\n";
foreach ($cupos as $cup) {
    echo "Carrera: {$cup->sigla} | 1ra Opcion: {$cup->cantidad_primera_opcion} | 2da Opcion: {$cup->cantidad_segunda_opcion}\n";
}
