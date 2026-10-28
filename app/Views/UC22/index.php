<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UC22: Cấu hình, sao lưu và giám sát hệ thống | QLTS Lớp 10</title>
    <link rel="stylesheet" href="assets/uc14.css">
    <style>
        .tab-nav { display: flex; gap: 8px; border-bottom: 2px solid var(--line); margin-bottom: 24px; }
        .tab-btn { padding: 12px 20px; font-weight: 700; color: var(--muted); border-bottom: 3px solid transparent; margin-bottom: -2px; }
        .tab-btn:hover { color: var(--blue); }
        .tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); }
    </style>
</head>
<body>
<div class="app-shell">
    <?php renderSidebar('uc22'); ?>

    <main class="main-area">
        <header class="topbar">
            <div>
                <span class="crumb">Hệ thống</span>
                <span class="slash">/</span>
                <b>UC22: Cấu hình, Sao lưu & Giám sát</b>
            </div>
            <div>
                <span class="pill pill-blue">Quản trị viên hệ thống</span>
            </div>
        </header>

        <div class="content">
            <div class="headline">
                <div>
                    <div class="eyebrow">QUẢN TRỊ KỸ THUẬT & VẬN HÀNH HỆ THỐNG</div>
                    <h1>Cấu hình, sao lưu và giám sát hệ thống</h1>
                    <p class="muted">Thiết lập tham số tuyển sinh, bảo đảm an toàn dữ liệu và giám sát nhật ký hoạt động.</p>
                </div>
            </div>

            <?php if (!empty($notice)): ?>
                <div class="alert <?= $notice['type'] === 'error' ? 'alert-error' : 'alert-success' ?>">
                    <?= h($notice['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Navigation 3 Tabs -->
            <div class="tab-nav">
                <a href="uc22.php?tab=config" class="tab-btn <?= $tab === 'config' ? 'active' : '' ?>">
                    ⚙️ 1. Cấu hình tham số tuyển sinh
                </a>
                <a href="uc22.php?tab=backup" class="tab-btn <?= $tab === 'backup' ? 'active' : '' ?>">
                    💾 2. Sao lưu dữ liệu & Phục hồi
                </a>
                <a href="uc22.php?tab=monitor" class="tab-btn <?= $tab === 'monitor' ? 'active' : '' ?>">
                    📊 3. Giám sát & Nhật ký sự kiện
                </a>
            </div>

            <!-- ============================================== -->
            <!-- TAB 1: CẤU HÌNH THAM SỐ HỆ THỐNG -->
            <!-- ============================================== -->
            <?php if ($tab === 'config'): ?>
                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Tham số tuyển sinh & Quy tắc vận hành</h2>
                            <p class="muted">Các tham số này ảnh hưởng trực tiếp đến quy trình đăng ký, điều chỉnh và xét tuyển lớp 10.</p>
                        </div>
                    </div>
                    <form method="post" action="uc22.php?tab=config" style="padding: 24px 28px;">
                        <?php hiddenCsrf(); ?>
                        <input type="hidden" name="action" value="save_config">

                        <div style="display: grid; gap: 20px;">
                            <?php foreach ($configs as $cfg): ?>
                                <div style="display: grid; grid-template-columns: 280px 1fr; gap: 16px; align-items: center; border-bottom: 1px solid #f0f3f8; padding-bottom: 16px;">
                                    <div>
                                        <b style="font-size: 13px; display:block;"><?= h($cfg['ten_cau_hinh']) ?></b>
                                        <span class="pill pill-blue" style="font-size: 10px; margin-top: 4px;"><?= h($cfg['nhom_cau_hinh']) ?></span>
                                        <small style="display:block; color:var(--muted); font-size:11px; margin-top:4px;"><?= h($cfg['mo_ta']) ?></small>
                                    </div>
                                    <div>
                                        <?php if ($cfg['kieu_du_lieu'] === 'boolean'): ?>
                                            <select name="configs[<?= h($cfg['khoa_cau_hinh']) ?>]" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; max-width:240px; background:white;">
                                                <option value="1" <?= $cfg['gia_tri'] === '1' ? 'selected' : '' ?>>Bật / Cho phép</option>
                                                <option value="0" <?= $cfg['gia_tri'] === '0' ? 'selected' : '' ?>>Tắt / Khóa chức năng</option>
                                            </select>
                                        <?php elseif ($cfg['kieu_du_lieu'] === 'date'): ?>
                                            <input type="text" name="configs[<?= h($cfg['khoa_cau_hinh']) ?>]" value="<?= h($cfg['gia_tri']) ?>" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; width:100%; max-width:320px;" placeholder="YYYY-MM-DD HH:MM:SS">
                                        <?php elseif ($cfg['kieu_du_lieu'] === 'number'): ?>
                                            <input type="number" name="configs[<?= h($cfg['khoa_cau_hinh']) ?>]" value="<?= h($cfg['gia_tri']) ?>" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; width:100%; max-width:200px;">
                                        <?php else: ?>
                                            <input type="text" name="configs[<?= h($cfg['khoa_cau_hinh']) ?>]" value="<?= h($cfg['gia_tri']) ?>" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; width:100%;">
                                        <?php endif; ?>
                                        <small style="color:var(--muted); font-size:11px; margin-left: 8px;">Khóa: <code><?= h($cfg['khoa_cau_hinh']) ?></code></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                            <button type="reset" class="btn btn-outline">Nhập lại</button>
                            <button type="submit" class="btn btn-primary">💾 Lưu toàn bộ cấu hình</button>
                        </div>
                    </form>
                </div>

            <!-- ============================================== -->
            <!-- TAB 2: SAO LƯU DỮ LIỆU -->
            <!-- ============================================== -->
            <?php elseif ($tab === 'backup'): ?>
                <div class="panel" style="margin-bottom: 24px; border-left: 4px solid #13764f;">
                    <div class="panel-head">
                        <div>
                            <h2>Tạo bản sao lưu dữ liệu mới</h2>
                            <p class="muted">Hệ thống sẽ trích xuất toàn bộ cấu trúc và dữ liệu của cơ sở dữ liệu `uc14_demo` thành file SQL an toàn.</p>
                        </div>
                        <form method="post" action="uc22.php?tab=backup" onsubmit="return confirm('Bạn có muốn tạo bản sao lưu dữ liệu ngay bây giờ?');">
                            <?php hiddenCsrf(); ?>
                            <input type="hidden" name="action" value="create_backup">
                            <button type="submit" class="btn btn-primary" style="background:#13764f;">
                                💾 Tạo bản sao lưu ngay
                            </button>
                        </form>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Lịch sử các bản sao lưu đã tạo</h2>
                            <p class="muted">Hiển thị danh sách các bản backup lưu trữ trong thư mục `database/backups/`</p>
                        </div>
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>MÃ</th>
                                    <th>TÊN FILE BẢN SAO LƯU</th>
                                    <th>DUNG LƯỢNG</th>
                                    <th>TRẠNG THÁI</th>
                                    <th>NGƯỜI THỰC HIỆN</th>
                                    <th>GHI CHÚ</th>
                                    <th>THỜI GIAN TẠO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($backups)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:30px; color:var(--muted);">
                                            Chưa có bản sao lưu nào được tạo.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($backups as $b): ?>
                                        <tr>
                                            <td>#<?= (int)$b['ma_sao_luu'] ?></td>
                                            <td>
                                                <b>📄 <?= h($b['ten_file']) ?></b>
                                                <small class="table-subtext">Đường dẫn: <?= h($b['duong_dan']) ?></small>
                                            </td>
                                            <td><span class="code-chip"><?= h($b['dung_luong']) ?></span></td>
                                            <td>
                                                <span class="pill pill-green">✓ Hoàn tất</span>
                                            </td>
                                            <td><?= h($b['nguoi_thuc_hien']) ?></td>
                                            <td><?= h($b['ghi_chu'] ?? '') ?></td>
                                            <td><?= h($b['thoi_gian_sao_luu']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ============================================== -->
            <!-- TAB 3: GIÁM SÁT HỆ THỐNG & NHẬT KÝ -->
            <!-- ============================================== -->
            <?php elseif ($tab === 'monitor'): ?>
                <div class="panel" style="margin-bottom: 24px;">
                    <div style="padding: 18px 25px;">
                        <form method="get" action="uc22.php" style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
                            <input type="hidden" name="tab" value="monitor">
                            
                            <div style="flex:1; min-width:240px;">
                                <input type="text" name="log_q" value="<?= h($logKeyword) ?>" placeholder="Tìm trong hành động hoặc nội dung chi tiết..." style="width:100%; border:1px solid #dce3ee; padding:9px 12px; border-radius:8px;">
                            </div>

                            <div>
                                <select name="log_type" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; background:white;">
                                    <option value="all">Tất cả loại sự kiện</option>
                                    <option value="DangNhap" <?= $logType === 'DangNhap' ? 'selected' : '' ?>>Đăng nhập</option>
                                    <option value="QuanTri" <?= $logType === 'QuanTri' ? 'selected' : '' ?>>Quản trị người dùng</option>
                                    <option value="HeThong" <?= $logType === 'HeThong' ? 'selected' : '' ?>>Cấu hình hệ thống</option>
                                    <option value="SaoLuu" <?= $logType === 'SaoLuu' ? 'selected' : '' ?>>Sao lưu dữ liệu</option>
                                    <option value="BaoMat" <?= $logType === 'BaoMat' ? 'selected' : '' ?>>Bảo mật & Đổi mật khẩu</option>
                                    <option value="Loi" <?= $logType === 'Loi' ? 'selected' : '' ?>>Lỗi hệ thống</option>
                                </select>
                            </div>

                            <div>
                                <button type="submit" class="btn btn-outline">Lọc sự kiện</button>
                                <?php if ($logKeyword !== '' || $logType !== 'all'): ?>
                                    <a href="uc22.php?tab=monitor" class="btn btn-ghost">Xóa lọc</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Nhật ký hoạt động & Giám sát bảo mật</h2>
                            <p class="muted">Hiển thị <?= count($logs) ?> sự kiện gần nhất được ghi nhận</p>
                        </div>
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>MÃ</th>
                                    <th>THỜI GIAN</th>
                                    <th>LOẠI SỰ KIỆN</th>
                                    <th>TÁC NHÂN</th>
                                    <th>HÀNH ĐỘNG</th>
                                    <th>CHI TIẾT</th>
                                    <th>ĐỊA CHỈ IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:30px; color:var(--muted);">
                                            Không có nhật ký nào phù hợp với bộ lọc.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $l): ?>
                                        <tr>
                                            <td>#<?= (int)$l['ma_nhat_ky'] ?></td>
                                            <td style="white-space:nowrap;"><?= h($l['thoi_gian']) ?></td>
                                            <td>
                                                <?php
                                                $pillClass = match ($l['loai_nhat_ky']) {
                                                    'DangNhap' => 'pill-blue',
                                                    'QuanTri' => 'pill-amber',
                                                    'SaoLuu' => 'pill-green',
                                                    'BaoMat' => 'pill-amber',
                                                    'Loi' => 'pill-red',
                                                    default => 'pill-blue',
                                                };
                                                ?>
                                                <span class="pill <?= $pillClass ?>">
                                                    <?= h($l['loai_nhat_ky']) ?>
                                                </span>
                                            </td>
                                            <td><b><?= h($l['nguoi_thuc_hien']) ?></b></td>
                                            <td><?= h($l['hanh_dong']) ?></td>
                                            <td style="color:#4a5b78;"><?= h($l['chi_tiet'] ?? '') ?></td>
                                            <td><small class="code-chip"><?= h($l['dia_chi_ip'] ?? '127.0.0.1') ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="footer-note">
                Chức năng UC22: Cấu hình, sao lưu và giám sát hệ thống — Quản trị viên hệ thống.
            </div>
        </div>
    </main>
</div>
</body>
</html>
