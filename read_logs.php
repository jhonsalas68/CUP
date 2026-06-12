<?php
$logPath = 'c:\Users\salas\Desktop\2do Parcial SI\storage\logs\laravel.log';
if (!file_exists($logPath)) {
    echo "Log file not found.\n";
    exit;
}

$content = file_get_contents($logPath);
$lines = explode("\n", $content);
$count = count($lines);
echo "Total lines in log: $count\n\n";

$idx = 0;
while (($idx = strpos($content, 'PublicPropertyNotFoundException', $idx)) !== false) {
    echo "--- MATCH AT CHAR $idx ---\n";
    // Find the line index
    $lineNum = substr_count(substr($content, 0, $idx), "\n") + 1;
    echo "Line: $lineNum\n";
    
    // Print lines around it
    $startLine = max(0, $lineNum - 3);
    $endLine = min(count($lines) - 1, $lineNum + 20);
    for ($i = $startLine; $i <= $endLine; $i++) {
        echo "$i: " . $lines[$i] . "\n";
    }
    $idx += strlen('PublicPropertyNotFoundException');
}
