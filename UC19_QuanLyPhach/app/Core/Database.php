<?php
declare(strict_types=1);

function connectDatabase(array $config): PDO
{
    return new PDO(sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['db_host'], $config['db_port'], $config['db_name']),
        $config['db_user'], $config['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
}
