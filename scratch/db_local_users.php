<?php

$users = ['postgres', 'root', 'admin', 'salas', 'system', 'uagrm'];
$passwords = ['postgres', 'root', 'admin', 'admin123', '123456', '1234', 'salas', 'jhonsalas68', ''];

foreach ($users as $user) {
    foreach ($passwords as $pwd) {
        try {
            $conn = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=postgres', $user, $pwd);
            echo "SUCCESS: connected as '$user' with password '$pwd'\n";
            exit;
        } catch (Exception $e) {
            // failed
        }
    }
}
echo "All local connections failed.\n";
