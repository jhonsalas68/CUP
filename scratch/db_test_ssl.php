<?php

$modes = ['disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'];

foreach ($modes as $mode) {
    echo "Testing mode: $mode ... ";
    try {
        $conn = new PDO("pgsql:host=thomas.proxy.rlwy.net;port=54541;dbname=railway;sslmode=$mode", "postgres", "uMupnvJmqGIblOmoDTSueGzodiWYCLtL");
        echo "SUCCESS!\n";
        exit;
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
