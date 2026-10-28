<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UC21: Quản lý tài khoản và phân quyền | QLTS Lớp 10</title>
    <link rel="stylesheet" href="assets/uc14.css">
</head>
<body>
<div class="app-shell">
    <?php renderSidebar('uc21'); ?>

    <main class="main-area">
        <header class="topbar">
            <div>
                <span class="crumb">Hệ thống</span>
                <span class="slash">/</span>
                <b>UC21: Quản lý tài khoản & Phân quyền</b>
            </div>
            <div>
                <span class="pill pill-blue">Quản trị viên hệ thống</span>
            </div>
        </header>

        <div class="content">
            <div class="headline">
                <div>
                    <div class="eyebrow">QUẢN TRỊ NGƯỜI DÙNG & PHÂN QUYỀN TRUY CẬP</div>
                    <h1>Quản lý tài khoản và phân quyền</h1>
                    <p class="muted">Tạo mới, chỉnh sửa thông tin, phân quyền chức năng và khóa/mở khóa tài khoản người dùng.</p>
                </div>
                <div>
                    <a href="#modal-create" class="btn btn-primary" onclick="document.getElementById('form-card').scrollIntoView({behavior: 'smooth'});">
                        ➕ Thêm mới tài khoản
                    </a>
                </div>
            </div>

            <?php if (!empty($notice)): ?>
                <div class="alert <?= $notice['type'] === 'error' ? 'alert-error' : 'alert-success' ?>">
                    <?= h($notice['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Form Thêm mới / Cập nhật tài khoản -->
            <div class="panel" id="form-card" style="margin-bottom: 24px; border-left: 4px solid var(--blue);">
                <div class="panel-head">
                    <div>
                        <h2><?= $editUser ? '✏️ Cập nhật thông tin tài khoản #' . $editUser['ma_nguoi_dung'] : '➕ Tạo tài khoản người dùng mới' ?></h2>
                        <p class="muted"><?= $editUser ? 'Thay đổi thông tin, phân lại quyền hoặc đổi mật khẩu cho người dùng' : 'Điền đầy đủ thông tin để cấp tài khoản mới trên hệ thống tuyển sinh' ?></p>
                    </div>
                    <?php if ($editUser): ?>
                        <a href="uc21.php" class="btn btn-ghost btn-small">Huỷ chỉnh sửa</a>
                    <?php endif; ?>
                </div>
                <div style="padding: 22px 25px;">
                    <form method="post" action="uc21.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                        <?php hiddenCsrf(); ?>
                        <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
                        <?php if ($editUser): ?>
                            <input type="hidden" name="id" value="<?= (int)$editUser['ma_nguoi_dung'] ?>">
                        <?php endif; ?>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Tên đăng nhập <span style="color:red;">*</span></label>
                            <input type="text" name="username" value="<?= h($editUser['ten_dang_nhap'] ?? '') ?>" <?= $editUser ? 'readonly style="background:#f4f6fa;"' : 'required' ?> placeholder="Ví dụ: canbo01" style="width:100%; border:1px solid #dce3ee; padding:10px 12px; border-radius:8px;">
                            <?php if ($editUser): ?>
                                <small style="color:var(--muted); font-size:11px;">Tên đăng nhập không thể thay đổi sau khi tạo.</small>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">
                                <?= $editUser ? 'Mật khẩu mới (bỏ trống nếu giữ nguyên)' : 'Mật khẩu khởi tạo *' ?>
                            </label>
                            <input type="password" name="password" placeholder="Nhập mật khẩu" <?= $editUser ? '' : 'required' ?> style="width:100%; border:1px solid #dce3ee; padding:10px 12px; border-radius:8px;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Họ và tên <span style="color:red;">*</span></label>
                            <input type="text" name="fullname" value="<?= h($editUser['ho_ten'] ?? '') ?>" required placeholder="Ví dụ: Nguyễn Văn A" style="width:100%; border:1px solid #dce3ee; padding:10px 12px; border-radius:8px;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Email liên hệ <span style="color:red;">*</span></label>
                            <input type="email" name="email" value="<?= h($editUser['email'] ?? '') ?>" required placeholder="user@domain.edu.vn" style="width:100%; border:1px solid #dce3ee; padding:10px 12px; border-radius:8px;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Số điện thoại</label>
                            <input type="text" name="phone" value="<?= h($editUser['so_dien_thoai'] ?? '') ?>" placeholder="0901234567" style="width:100%; border:1px solid #dce3ee; padding:10px 12px; border-radius:8px;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Vai trò hệ thống <span style="color:red;">*</span></label>
                            <select name="role" required style="width:100%; border:1px solid #dce3ee; padding:10px 12px; border-radius:8px; background:white;">
                                <?php
                                $roles = [
                                    'CanBoSo' => 'Cán bộ Sở GD&ĐT',
                                    'BanTuyenSinhTHPT' => 'Ban tuyển sinh THPT',
                                    'GiaoVuTHCS' => 'Nhân viên giáo vụ THCS',
                                    'ThuKy' => 'Thư ký hội đồng thi',
                                    'QuanTriVien' => 'Quản trị viên hệ thống',
                                    'HocSinh' => 'Học sinh / Thí sinh',
                                ];
                                $currentRole = $editUser['vai_tro'] ?? 'CanBoSo';
                                foreach ($roles as $rKey => $rLabel):
                                ?>
                                    <option value="<?= $rKey ?>" <?= $rKey === $currentRole ? 'selected' : '' ?>><?= $rLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label style="display:block; font-size:12px; font-weight:700; margin-bottom:6px;">Phân quyền mã Use Case (cách nhau bởi dấu phẩy)</label>
                            <input type="text" name="perms" value="<?= h($editUser['quyen_han'] ?? 'UC09,UC14,UC21,UC22') ?>" placeholder="Ví dụ: UC09,UC14,UC21,UC22 hoặc ALL" style="width:100%; border:1px solid #dce3ee; padding:10px 12px; border-radius:8px;">
                            <small style="color:var(--muted); font-size:11px;">Mã chức năng người dùng được phép thực hiện.</small>
                        </div>

                        <div style="grid-column: 1 / -1; display:flex; gap:12px; justify-content:flex-end; margin-top:8px;">
                            <?php if ($editUser): ?>
                                <a href="uc21.php" class="btn btn-outline">Hủy</a>
                                <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">➕ Thêm tài khoản mới</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bộ lọc tìm kiếm tài khoản -->
            <div class="panel" style="margin-bottom: 24px;">
                <div style="padding: 18px 25px;">
                    <form method="get" action="uc21.php" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
                        <div style="flex: 1; min-width: 220px;">
                            <input type="text" name="q" value="<?= h($keyword) ?>" placeholder="Tìm theo tên đăng nhập, họ tên hoặc email..." style="width:100%; border:1px solid #dce3ee; padding:9px 12px; border-radius:8px;">
                        </div>

                        <div>
                            <select name="role" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; background:white;">
                                <option value="all">Tất cả vai trò</option>
                                <?php foreach ($roles as $rKey => $rLabel): ?>
                                    <option value="<?= $rKey ?>" <?= $rKey === $filterRole ? 'selected' : '' ?>><?= $rLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <select name="status" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; background:white;">
                                <option value="all">Tất cả trạng thái</option>
                                <option value="HoatDong" <?= $filterStatus === 'HoatDong' ? 'selected' : '' ?>>Đang hoạt động</option>
                                <option value="DaKhoa" <?= $filterStatus === 'DaKhoa' ? 'selected' : '' ?>>Đã khóa</option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-outline">Tìm kiếm</button>
                            <?php if ($keyword !== '' || $filterRole !== 'all' || $filterStatus !== 'all'): ?>
                                <a href="uc21.php" class="btn btn-ghost">Xóa lọc</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danh sách người dùng -->
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Danh sách người dùng và phân quyền</h2>
                        <p class="muted">Hiển thị tổng số <?= count($users) ?> tài khoản trong hệ thống</p>
                    </div>
                </div>

                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>MÃ</th>
                                <th>TÊN ĐĂNG NHẬP</th>
                                <th>HỌ VÀ TÊN</th>
                                <th>VAI TRÒ</th>
                                <th>LIÊN HỆ (EMAIL / SĐT)</th>
                                <th>QUYỀN HẠN</th>
                                <th>TRẠNG THÁI</th>
                                <th>THAO TÁC QUẢN TRỊ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:30px; color:var(--muted);">
                                        Không tìm thấy tài khoản nào phù hợp với điều kiện tìm kiếm.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>#<?= (int)$u['ma_nguoi_dung'] ?></td>
                                        <td><span class="code-chip"><?= h($u['ten_dang_nhap']) ?></span></td>
                                        <td><b><?= h($u['ho_ten']) ?></b></td>
                                        <td>
                                            <span class="pill pill-blue">
                                                <?= h($roles[$u['vai_tro']] ?? $u['vai_tro']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= h($u['email']) ?>
                                            <small class="table-subtext">📞 <?= h($u['so_dien_thoai']) ?></small>
                                        </td>
                                        <td>
                                            <span style="font-family:monospace; font-size:11px; background:#f4f6fa; padding:3px 6px; border-radius:4px;">
                                                <?= h($u['quyen_han'] ?: 'Mặc định') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($u['trang_thai'] === 'HoatDong'): ?>
                                                <span class="pill pill-green">✓ Hoạt động</span>
                                            <?php else: ?>
                                                <span class="pill pill-red">🔒 Đã khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:8px;">
                                                <a href="uc21.php?action=edit&id=<?= (int)$u['ma_nguoi_dung'] ?>" class="btn btn-outline btn-small">
                                                    ✏️ Sửa
                                                </a>

                                                <form method="post" action="uc21.php" onsubmit="return confirm('Bạn có chắc chắn muốn <?= $u['trang_thai'] === 'HoatDong' ? 'KHÓA' : 'MỞ KHÓA' ?> tài khoản này không?');">
                                                    <?php hiddenCsrf(); ?>
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?= (int)$u['ma_nguoi_dung'] ?>">
                                                    <?php if ($u['trang_thai'] === 'HoatDong'): ?>
                                                        <button type="submit" class="btn btn-outline-danger btn-small">🔒 Khóa</button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-outline btn-small" style="color:#13764f; border-color:#bdebd3;">🔓 Mở</button>
                                                    <?php endif; ?>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer-note">
                Chức năng UC21: Quản lý tài khoản và phân quyền — Quản trị viên hệ thống (Mọi thao tác thay đổi đều được lưu nhật ký).
            </div>
        </div>
    </main>
</div>
</body>
</html>
