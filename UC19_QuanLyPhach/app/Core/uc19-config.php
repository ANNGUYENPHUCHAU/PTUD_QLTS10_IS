<?php
return array_replace([
    'db_host' => '127.0.0.1', 'db_port' => '3306',
    'db_name' => 'uc19_demo', 'db_user' => 'root', 'db_password' => '',
], is_file(__DIR__ . '/uc19-config.local.php') ? require __DIR__ . '/uc19-config.local.php' : []);
