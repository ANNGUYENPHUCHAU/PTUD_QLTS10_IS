<?php
/** Cấu hình demo UC14. Dùng uc14-config.local.php khi cần thay đổi theo máy. */
return array_replace([
    'db_host' => '127.0.0.1',
    'db_port' => '3306',
    'db_name' => 'uc14_demo',
    'db_user' => 'root',
    'db_password' => '',
], file_exists(__DIR__ . '/uc14-config.local.php') ? require __DIR__ . '/uc14-config.local.php' : []);
