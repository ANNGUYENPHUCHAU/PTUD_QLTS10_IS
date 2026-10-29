<?php
declare(strict_types=1);
final class Database
{
    public static function connect(): PDO
    {
        if (!extension_loaded('pdo_sqlite')) {
            throw new RuntimeException('Hãy bật extension pdo_sqlite trong php.ini rồi khởi động lại Apache.');
        }
        $config = require __DIR__ . '/uc11-config.php';
        $directory = dirname($config['database_path']);
        if (!is_dir($directory) && !mkdir($directory, 0770, true)) {
            throw new RuntimeException('Không thể tạo thư mục storage.');
        }
        $db = new PDO('sqlite:' . $config['database_path']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA busy_timeout = 5000');
        return $db;
    }
}
