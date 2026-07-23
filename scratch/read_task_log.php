<?php

$dir = 'C:\Users\salas\.gemini\antigravity-ide\brain\e47426af-ff9d-4afa-8e0d-01c9e0ef0725\.system_generated\tasks';
if (! is_dir($dir)) {
    echo "Directory does not exist.\n";
    exit;
}
$files = scandir($dir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "File: $file\n";
        echo file_get_contents($dir.DIRECTORY_SEPARATOR.$file)."\n";
    }
}
