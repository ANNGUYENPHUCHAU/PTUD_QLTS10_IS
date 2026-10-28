<?php

$config = require dirname(__DIR__, 3) . '/config/config.php';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $config['app_name']) ?> · <?= e($config['app_name']) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<header class="topbar">
    <div class="container topbar__content">
        <a class="brand" href="index.php">
            <span class="brand__mark">TS</span>
            <span><strong>Hệ thống tuyển sinh</strong><small>Sở Giáo dục và Đào tạo</small></span>
        </a>
        <div class="user">
            <span class="user__avatar">CB</span>
            <span><strong><?= e($_SESSION['user_name']) ?></strong><small>Cán bộ tuyển sinh</small></span>
        </div>
    </div>
</header>
<main class="container main">
    <?php if (!empty($flash)): ?>
        <div class="alert alert--<?= e($flash['type']) ?>" role="alert">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

