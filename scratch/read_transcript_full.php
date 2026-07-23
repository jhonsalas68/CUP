<?php

$logPath = 'C:\Users\salas\.gemini\antigravity-ide\brain\e47426af-ff9d-4afa-8e0d-01c9e0ef0725\.system_generated\logs\transcript.jsonl';
if (! file_exists($logPath)) {
    echo "Log file not found.\n";
    exit;
}

$lines = file($logPath);
$total = count($lines);
echo 'Total log lines in current conversation: '.$total."\n";

for ($i = 0; $i < $total; $i++) {
    $data = json_decode($lines[$i], true);
    $type = $data['type'] ?? 'unknown';
    $content = trim($data['content'] ?? '');
    $contentPreview = str_replace(["\r", "\n"], ' ', substr($content, 0, 150));
    echo "[$i] Type: $type | Content: $contentPreview\n";
}
