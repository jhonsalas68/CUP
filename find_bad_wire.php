<?php
$content = file_get_contents('resources/views/livewire/admin/examenes.blade.php');
$lines = explode("\n", $content);

foreach ($lines as $lineNum => $line) {
    // Match any wire:attribute="value"
    if (preg_match_all('/wire:[a-zA-Z0-9\.\-]+\s*=\s*["\']([^"\']+)["\']/', $line, $matches)) {
        foreach ($matches[1] as $val) {
            if (str_contains($val, '$')) {
                // Check if it's NOT a valid Livewire magic method
                if (!preg_match('/^\$(set|toggle|refresh|parent|event|dispatch)/', $val)) {
                    echo "Line " . ($lineNum + 1) . ": Malformed binding: " . htmlspecialchars($line) . "\n";
                }
            }
        }
    }
}
