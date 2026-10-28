<?php
/**
 * Cấu hình DEMO dành riêng cho môi trường XAMPP ở máy cá nhân.
 * Không đưa mật khẩu CSDL thật lên GitHub. Có thể tạo config.local.php để ghi đè.
 */
return array_replace([
    'db_host' => '127.0.0.1',
    'db_port' => '3306',
    'db_name' => 'uc14_demo',
    'db_user' => 'root',
    'db_password' => '',
], file_exists(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : []);
