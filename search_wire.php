<?php

$content = file_get_contents('resources/views/livewire/admin/examenes.blade.php');
$offset = 0;
while (($offset = strpos($content, '$', $offset)) !== false) {
    if ($offset < 26000) {
        echo "Position $offset: ".substr($content, max(0, $offset - 50), 100)."\n";
    }
    $offset++;
}
