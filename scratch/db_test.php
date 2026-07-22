<?php

$passwords = ['123456', 'root', 'admin123', '1234', '12345678', 'postgres123', 'salas', 'jhonsalas68'];

foreach ($passwords as $pwd) {
    try {
        $conn = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=postgres", "postgres", $pwd);
        echo "Connection to local with password '{$pwd}' OK!\n";
        exit;
    } catch (Exception $e) {
        // failed
    }
}
echo "All common passwords failed.\n";
