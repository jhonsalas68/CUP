<?php

function normalizeNumbers($text)
{
    $words = [
        'cero' => 0, 'uno' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4, 'cinco' => 5,
        'seis' => 6, 'siete' => 7, 'ocho' => 8, 'nueve' => 9, 'diez' => 10,
        'once' => 11, 'doce' => 12, 'trece' => 13, 'catorce' => 14, 'quince' => 15,
        'dieciséis' => 16, 'dieciseis' => 16, 'diecisiete' => 17, 'dieciocho' => 18, 'diecinueve' => 19,
        'veinte' => 20, 'veintiuno' => 21, 'veintidós' => 22, 'veintidos' => 22, 'veintitres' => 23, 'veintitrés' => 23,
        'veinticuatro' => 24, 'veinticinco' => 25, 'veintiséis' => 26, 'veintiseis' => 26, 'veintisiete' => 27,
        'veintiocho' => 28, 'veintinueve' => 29, 'treinta' => 30, 'cuarenta' => 40, 'cincuenta' => 50,
        'sesenta' => 60, 'setenta' => 70, 'ochenta' => 80, 'noventa' => 90, 'cien' => 100
    ];
    
    $tens = [
        'treinta' => 30,
        'cuarenta' => 40,
        'cincuenta' => 50,
        'sesenta' => 60,
        'setenta' => 70,
        'ochenta' => 80,
        'noventa' => 90
    ];
    $units = [
        'uno' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4, 'cinco' => 5,
        'seis' => 6, 'siete' => 7, 'ocho' => 8, 'nueve' => 9
    ];
    
    foreach ($tens as $tenWord => $tenVal) {
        foreach ($units as $unitWord => $unitVal) {
            $text = preg_replace('/\b' . $tenWord . '\s+y\s+' . $unitWord . '\b/u', $tenVal + $unitVal, $text);
        }
    }
    
    foreach ($words as $word => $num) {
        $text = preg_replace('/\b' . $word . '\b/u', $num, $text);
    }
    
    return $text;
}

$transcript = 'nota ponderada mayor de setenta';
$transcript = mb_strtolower($transcript, 'UTF-8');
$normalized = normalizeNumbers($transcript);
echo "Normalized: '" . $normalized . "'\n";

$matched = preg_match('/nota\s+(?:ponderada\s+)?(?:mayor|superior|más\s+de|mas\s+de)\s+(?:a\s+|de\s+)?(\d+)/', $normalized, $matches);
echo "Matched: " . ($matched ? "YES" : "NO") . "\n";
if ($matched) {
    echo "Value: " . $matches[1] . "\n";
}
