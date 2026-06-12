<?php
$logPath = 'C:\Users\salas\.gemini\antigravity-ide\brain\f5097646-a4c2-4615-883c-2b8a29589f6d\.system_generated\logs\transcript.jsonl';
if (!file_exists($logPath)) {
    echo "Log file not found.\n";
    exit;
}

$lines = file($logPath);
echo "Total log lines: " . count($lines) . "\n";
// Let's print the last 20 lines
$lastLines = array_slice($lines, -20);
foreach ($lastLines as $idx => $line) {
    $data = json_decode($line, true);
    echo "--- LOG LINE " . (count($lines) - 20 + $idx) . " ---\n";
    echo "Type: " . ($data['type'] ?? 'unknown') . "\n";
    if (isset($data['content'])) {
        echo "Content preview: " . substr($data['content'], 0, 300) . "\n";
    }
}
