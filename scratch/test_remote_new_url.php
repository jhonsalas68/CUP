<?php

echo "Testing remote connection with correct host/port/password...\n";
try {
    $conn = new PDO("pgsql:host=hayabusa.proxy.rlwy.net;port=52335;dbname=railway", "postgres", "OmESmxJfONZkxyNuxFfSGNtRlaobjDug");
    echo "SUCCESS!\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
