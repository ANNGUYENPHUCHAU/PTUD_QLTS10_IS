<?php

declare(strict_types=1);

use App\Controllers\AdmissionController;
use App\Core\Autoloader;
use App\Core\Database;

require dirname(__DIR__) . '/app/Core/Autoloader.php';
Autoloader::register();

$config = require dirname(__DIR__) . '/config/config.php';
date_default_timezone_set($config['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
}

// UC16 bắt đầu sau khi cán bộ đã đăng nhập; tài khoản này mô phỏng actor đang đăng nhập.
$_SESSION['user_name'] ??= 'Nguyễn Tuấn Anh - Phòng Quản lý tuyển sinh';

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$db = Database::connect($config['db']);
$schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
if ($schema === false) {
    throw new RuntimeException('Không đọc được cấu trúc cơ sở dữ liệu.');
}
$db->exec($schema);
if ((int) $db->query('SELECT COUNT(*) FROM admission_rounds')->fetchColumn() === 0) {
    $seed = file_get_contents(dirname(__DIR__) . '/database/seed.sql');
    if ($seed === false) {
        throw new RuntimeException('Không đọc được dữ liệu mẫu.');
    }
    $db->exec($seed);
}

$controller = new AdmissionController($db);
$action = $_GET['action'] ?? 'index';

match ($action) {
    'index' => $controller->index(),
    'run' => $controller->run(),
    'results' => $controller->results(),
    'school' => $controller->school(),
    'confirm' => $controller->confirm(),
    default => (function () use ($controller): void {
        http_response_code(404);
        $controller->index();
    })(),
};

