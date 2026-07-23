<?php

$content = file_get_contents('resources/views/livewire/admin/examenes.blade.php');
$lines = explode("\n", $content);

$offset = 0;
while (($offset = strpos($content, 'wire:model', $offset)) !== false) {
    // Find the line number
    $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Line $lineNum: ".trim($lines[$lineNum - 1])."\n";
    $offset += strlen('wire:model');
}
