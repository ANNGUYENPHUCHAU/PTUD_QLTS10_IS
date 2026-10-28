<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập | UC14</title><link rel="stylesheet" href="assets/uc14.css"></head>
<body class="login-page">
  <main class="login-card">
    <div class="brand-logo large">10</div>
    <p class="eyebrow">HỆ THỐNG QUẢN LÝ TUYỂN SINH LỚP 10</p>
    <h1>Đăng nhập cán bộ</h1>
    <p class="muted">Bản demo chức năng cấp số báo danh và phân phòng thi (UC14).</p>
    <?php if ($notice): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-error' : 'alert-success' ?>"><?= h($notice['message']) ?></div><?php endif; ?>
    <form method="post" class="login-form">
      <?php hiddenCsrf(); ?><input type="hidden" name="action" value="login">
      <label>Tên đăng nhập <input type="text" name="username" placeholder="Nhập tài khoản" required autocomplete="username"></label>
      <label>Mật khẩu <input type="password" name="password" placeholder="Nhập mật khẩu" required autocomplete="current-password"></label>
      <button class="btn btn-primary btn-full">Đăng nhập</button>
    </form>
    <div class="demo-credentials"><b>Tài khoản demo:</b> <code>canbo</code> &nbsp;|&nbsp; <b>Mật khẩu:</b> <code>Demo@123</code></div>
    <p class="footnote">Chỉ dùng để trình diễn cục bộ — không dùng cơ chế đăng nhập mẫu này khi triển khai thật.</p>
  </main>
</body></html>
