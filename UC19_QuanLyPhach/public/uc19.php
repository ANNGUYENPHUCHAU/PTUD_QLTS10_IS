<?php
declare(strict_types=1);
session_name('uc19_phach_session');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'use_strict_mode' => true]);
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
date_default_timezone_set('Asia/Ho_Chi_Minh');
foreach (['Core/Database', 'Core/uc19-helpers', 'Models/UC19Model', 'Services/UC19Service', 'Controllers/UC19Controller'] as $file) {
    require_once dirname(__DIR__) . '/app/' . $file . '.php';
}
(new UC19Controller())->run();
