<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bitacora;

$logs = Bitacora::orderBy('created_at', 'desc')->take(10)->get();

echo "Last 10 Bitacora logs:\n";
foreach ($logs as $l) {
    echo "[{$l->created_at}] Action: {$l->action} | Object: {$l->objeto} | Desc: {$l->descripcion}\n";
}
