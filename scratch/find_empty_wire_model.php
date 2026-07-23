<?php

$dir = new RecursiveDirectoryIterator(__DIR__.'/../resources/views');
$iterator = new RecursiveIteratorIterator($dir);
$regex = '/wire:model[^\s>]*+/';

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());

        // Find all occurrences of wire:model
        $offset = 0;
        while (($offset = strpos($content, 'wire:model', $offset)) !== false) {
            // Find the character after wire:model
            $sub = substr($content, $offset, 100);

            // Get the line number
            $line = substr_count(substr($content, 0, $offset), "\n") + 1;

            echo 'File: '.basename($file->getPathname())." | Line: $line | Match: ".htmlspecialchars(trim(explode("\n", $sub)[0]))."\n";

            $offset += strlen('wire:model');
        }
    }
}
