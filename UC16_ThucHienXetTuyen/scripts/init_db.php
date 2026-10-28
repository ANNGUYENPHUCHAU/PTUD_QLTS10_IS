<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Database;

Autoloader::register();
$config = require dirname(__DIR__) . '/config/config.php';
$pdo = Database::connect($config['db']);
$schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
if ($schema === false) {
    throw new RuntimeException('Không đọc được schema.sql');
}
$pdo->exec($schema);

$count = (int) $pdo->query('SELECT COUNT(*) FROM admission_rounds')->fetchColumn();
if ($count === 0) {
    $seed = file_get_contents(dirname(__DIR__) . '/database/seed.sql');
    if ($seed === false) {
        throw new RuntimeException('Không đọc được seed.sql');
    }
    $pdo->exec($seed);
    echo "Đã khởi tạo cơ sở dữ liệu và dữ liệu mẫu.\n";
} else {
    echo "Cơ sở dữ liệu đã tồn tại; không nạp lại dữ liệu mẫu.\n";
}

