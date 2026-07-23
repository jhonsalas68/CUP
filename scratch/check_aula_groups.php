<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Aula;
use App\Models\Grupo;
use Illuminate\Contracts\Console\Kernel;

try {
    $aulas = Aula::all();
    echo "=== Aulas in Database ===\n";
    foreach ($aulas as $aula) {
        $groupCount = Grupo::whereHas('horarios', function ($query) use ($aula) {
            $query->where('aula_id', $aula->id);
        })->count();
        echo "Aula ID: {$aula->id} | Name: {$aula->nombre} | Groups: {$groupCount}\n";
    }
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
