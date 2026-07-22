<?php

echo "Testing remote connection with new password...\n";
try {
    $conn = new PDO("pgsql:host=thomas.proxy.rlwy.net;port=54541;dbname=railway", "postgres", "OmESmxJfONZkxyNuxFfSGNtRlaobjDug");
    echo "SUCCESS!\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
