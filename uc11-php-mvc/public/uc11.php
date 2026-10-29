<?php
declare(strict_types=1);
$config = require dirname(__DIR__) . '/app/Core/uc11-config.php';
date_default_timezone_set($config['timezone']);
session_start([
    'use_strict_mode' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
require dirname(__DIR__) . '/app/Core/uc11-helpers.php';
require dirname(__DIR__) . '/app/Core/Database.php';
require dirname(__DIR__) . '/app/Models/UC11Model.php';
require dirname(__DIR__) . '/app/Services/UC11Service.php';
require dirname(__DIR__) . '/app/Controllers/UC11Controller.php';
try {
    $controller = new UC11Controller(new UC11Service(new UC11Model(Database::connect())));
    $controller->handle();
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    $view = 'error';
    $message = 'Không thể tải dữ liệu đợt tuyển sinh. Kiểm tra PHP PDO SQLite và quyền ghi thư mục storage theo README.';
    require dirname(__DIR__) . '/app/Views/UC11/layout.php';
}
