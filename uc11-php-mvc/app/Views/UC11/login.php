<section class="login"><h2>Đăng nhập cán bộ</h2>
<p>Tài khoản demo: <strong>canbo</strong> · Mật khẩu: <strong>Demo@123</strong></p>
<form method="post" action="uc11.php?action=login">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<label for="username">Tài khoản</label><input id="username" name="username" autocomplete="username" required>
<label for="password">Mật khẩu</label><input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="submit">Đăng nhập</button>
</form></section>
