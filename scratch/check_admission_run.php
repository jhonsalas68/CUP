<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AdmissionSelectionService;
use App\Models\Gestion;

$gestion = Gestion::where('activo', true)->first();
if (!$gestion) {
    echo "No active gestion found.\n";
    exit;
}

echo "Running processAdmissions for gestion: {$gestion->nombre} (ID: {$gestion->id})\n";

try {
    $service = new AdmissionSelectionService();
    $result = $service->processAdmissions($gestion->id);
    echo "Admission process completed successfully!\n";
    print_r($result);
} catch (\Exception $e) {
    echo "Error running admission process: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
