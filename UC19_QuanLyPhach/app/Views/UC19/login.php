<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Đăng nhập · UC19</title><link rel="stylesheet" href="assets/uc19.css"></head>
<body class="login-page"><main class="login-card"><span class="brand-mark">TS</span><p class="eyebrow">TUYỂN SINH LỚP 10</p><h1>Quản lý phách bài thi</h1><p class="muted">Đăng nhập tài khoản thư ký có quyền đánh phách.</p>
<?php if ($notice): ?><div role="alert" class="notice <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif ?>
<form method="post"><?php formFields('login') ?><label>Tên đăng nhập<input name="username" autocomplete="username" required autofocus></label><label>Mật khẩu<input type="password" name="password" autocomplete="current-password" required></label><button class="primary full">Đăng nhập</button></form>
<div class="demo-note">Tài khoản demo: <strong>thuky</strong> / <strong>ThuKy@123</strong><br>Chỉ dùng cho bài tập trên máy cục bộ.</div></main></body></html>
