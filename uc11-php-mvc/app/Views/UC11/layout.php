<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>UC11 | Quản lý tuyển sinh lớp 10</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body><main>
<header><span class="eyebrow">HỆ THỐNG QUẢN LÝ TUYỂN SINH LỚP 10</span>
<h1>Thiết lập và quản lý đợt, lịch tuyển sinh</h1>
<p>UC11 · Demo PHP MVC · Thời gian Việt Nam</p></header>
<?php if (!empty($message)): ?><p role="alert" class="message"><?= e($message) ?></p><?php endif; ?>
<?php require __DIR__ . '/' . $view . '.php'; ?>
<footer>Demo phục vụ học tập · PHP và SQLite</footer>
</main></body></html>
