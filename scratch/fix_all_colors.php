<?php

$dir = __DIR__.'/../resources/views/livewire';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$mapping = [
    'bg-violet-650' => 'bg-violet-600',
    'hover:bg-violet-750' => 'hover:bg-violet-700',
    'text-violet-650' => 'text-violet-650', // Wait, let's map text-violet-650 to text-violet-600
    'text-violet-650' => 'text-violet-600',
    'text-indigo-650' => 'text-indigo-600',
    'bg-indigo-650' => 'bg-indigo-600',
    'border-zinc-150' => 'border-zinc-200',
    'border-zinc-850' => 'border-zinc-800',
    'text-zinc-450' => 'text-zinc-500',
    'text-zinc-650' => 'text-zinc-600',
    'text-zinc-750' => 'text-zinc-700',
    'text-zinc-550' => 'text-zinc-500',
    'text-emerald-650' => 'text-emerald-600',
    'text-rose-455' => 'text-rose-500',
    'text-rose-450' => 'text-rose-400',
    'text-emerald-450' => 'text-emerald-400',
    'border-rose-250' => 'border-rose-300',
    'border-emerald-250' => 'border-emerald-300',
    'bg-zinc-750' => 'bg-zinc-700',
    'text-zinc-750' => 'text-zinc-700',
    'divide-zinc-150' => 'divide-zinc-200',
    'divide-zinc-850' => 'divide-zinc-800',
];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        $original = $content;

        foreach ($mapping as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo 'Updated colors in: '.basename($path)."\n";
        }
    }
}
echo "Done color fixes!\n";
