<?php

$hosts = ['localhost', '127.0.0.1', '::1'];
$passwords = ['postgres', 'root', 'admin', 'admin123', '123456', '1234', 'salas', 'jhonsalas68', ''];

foreach ($hosts as $host) {
    foreach ($passwords as $pwd) {
        try {
            $conn = new PDO("pgsql:host=$host;port=5432;dbname=postgres", "postgres", $pwd);
            echo "SUCCESS: connected to '$host' as 'postgres' with password '$pwd'\n";
            exit;
        } catch (Exception $e) {
            // failed
        }
    }
}
echo "All local connections failed.\n";
