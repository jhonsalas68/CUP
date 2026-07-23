<?php

$content = file_get_contents('resources/views/livewire/admin/examenes.blade.php');

// Regex to find HTML tags (e.g. <flux:input ... > or <input ... >)
preg_match_all('/<[a-zA-Z0-9\:\-\_]+[^>]*>/i', $content, $matches);

foreach ($matches[0] as $tag) {
    if (str_contains($tag, '$')) {
        echo 'Found tag containing $: '.htmlspecialchars($tag)."\n\n";
    }
}
