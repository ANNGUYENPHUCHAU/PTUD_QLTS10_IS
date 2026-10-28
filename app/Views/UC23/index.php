<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UC23: Quên mật khẩu | QLTS Lớp 10</title>
    <link rel="stylesheet" href="assets/uc14.css">
</head>
<body class="login-page">
    <main class="login-card" style="width: min(100%, 520px);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div class="brand-logo large" style="margin-bottom: 0;">🔑</div>
            <a href="uc14.php" class="btn btn-ghost btn-small">← Quay lại trang chính</a>
        </div>

        <p class="eyebrow">HỆ THỐNG QUẢN LÝ TUYỂN SINH LỚP 10</p>
        <h1>Quên mật khẩu</h1>
        <p class="muted">Xác minh danh tính tài khoản và thiết lập lại mật khẩu đăng nhập an toàn.</p>

        <?php if (!empty($notice)): ?>
            <div class="alert <?= $notice['type'] === 'error' ? 'alert-error' : 'alert-success' ?>">
                <?= h($notice['message']) ?>
            </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- BƯỚC 1: NHẬP THÔNG TIN XÁC MINH DANH TÍNH -->
        <!-- ========================================== -->
        <?php if ($step === 1): ?>
            <form method="post" action="uc23.php" class="login-form">
                <?php hiddenCsrf(); ?>
                <input type="hidden" name="action" value="verify">

                <div style="background: #eff5ff; border-radius: 10px; padding: 12px 14px; font-size: 12px; color: #245cc5; margin-bottom: 8px;">
                    ℹ️ Bước 1/2: Nhập tên đăng nhập cùng Email hoặc Số điện thoại bạn đã đăng ký để hệ thống đối chiếu.
                </div>

                <label>
                    Tên đăng nhập
                    <input type="text" name="username" placeholder="Ví dụ: canbo, admin, giaovu_nd" required autocomplete="username">
                </label>

                <label>
                    Email hoặc Số điện thoại đã đăng ký
                    <input type="text" name="email_or_phone" placeholder="Ví dụ: canbo@sogddt.edu.vn hoặc 0912345678" required>
                </label>

                <button type="submit" class="btn btn-primary btn-full">
                    Tiếp tục xác minh →
                </button>
            </form>

            <div class="demo-credentials" style="margin-top: 20px;">
                <b>Tài khoản mẫu để thử nghiệm:</b><br>
                • <code>canbo</code> &nbsp;|&nbsp; Email: <code>canbo@sogddt.edu.vn</code> (hoặc SĐT: <code>0912345678</code>)<br>
                • <code>admin</code> &nbsp;|&nbsp; Email: <code>admin@tuyensinh10.edu.vn</code> (hoặc SĐT: <code>0901234567</code>)
            </div>

        <!-- ========================================== -->
        <!-- BƯỚC 2: THIẾT LẬP MẬT KHẨU MỚI -->
        <!-- ========================================== -->
        <?php elseif ($step === 2 && $verifiedUser): ?>
            <div style="background: #e7faf0; border: 1px solid #bdebd3; border-radius: 10px; padding: 14px; margin-bottom: 20px;">
                <b style="color: #18623e;">✓ Xác minh thành công:</b>
                <div style="font-size: 13px; color: #18623e; margin-top: 4px;">
                    Tài khoản: <b><?= h($verifiedUser['ten_dang_nhap']) ?></b> (<?= h($verifiedUser['ho_ten']) ?>)
                </div>
            </div>

            <form method="post" action="uc23.php" class="login-form">
                <?php hiddenCsrf(); ?>
                <input type="hidden" name="action" value="reset_password">

                <div style="background: #eff5ff; border-radius: 10px; padding: 12px 14px; font-size: 12px; color: #245cc5; margin-bottom: 8px;">
                    ℹ️ Bước 2/2: Đặt mật khẩu mới (tối thiểu 6 ký tự).
                </div>

                <label>
                    Mật khẩu mới
                    <input type="password" name="new_password" placeholder="Nhập mật khẩu mới" required minlength="6" autocomplete="new-password">
                </label>

                <label>
                    Xác nhận mật khẩu mới
                    <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required minlength="6" autocomplete="new-password">
                </label>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="btn btn-primary btn-full">
                        💾 Xác nhận đặt lại mật khẩu
                    </button>
                </div>
            </form>

            <form method="post" action="uc23.php" style="margin-top: 14px; text-align: center;">
                <?php hiddenCsrf(); ?>
                <input type="hidden" name="action" value="restart">
                <button type="submit" class="btn btn-ghost btn-small">Xác minh tài khoản khác</button>
            </form>

        <!-- ========================================== -->
        <!-- BƯỚC 3: HOÀN TẤT ĐẶT LẠI MẬT KHẨU -->
        <!-- ========================================== -->
        <?php else: ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 48px; margin-bottom: 12px;">🎉</div>
                <h2 style="margin: 0 0 8px;">Đổi mật khẩu thành công!</h2>
                <p class="muted" style="margin-bottom: 24px;">Bạn đã có thể sử dụng mật khẩu mới vừa thiết lập để đăng nhập vào hệ thống.</p>

                <div style="display: flex; gap: 12px; justify-content: center;">
                    <a href="uc14.php" class="btn btn-primary" style="min-width: 160px;">Đến màn hình Đăng nhập</a>
                    <a href="uc23.php?action=restart" class="btn btn-outline">Thực hiện lại</a>
                </div>
            </div>
        <?php endif; ?>

        <p class="footnote" style="text-align: center; margin-top: 24px;">
            Chức năng UC23: Quên mật khẩu — Hệ thống Quản lý tuyển sinh lớp 10 (Thái Bá Cường).
        </p>
    </main>
</body>
</html>
