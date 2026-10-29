<?php
/**
 * Sidebar điều hướng cho UC09: Thống kê kết quả tuyển sinh
 * @param string $activeKey 'uc09'
 */
function renderSidebar(string $activeKey = 'uc09'): void {
    $user = $_SESSION['user'] ?? ['username' => 'bantuyensinh_nh', 'fullname' => 'Thái Bá Cường', 'role' => 'Ban Tuyển Sinh THPT'];
    $rootDir = dirname(__DIR__, 3);
    $hasUc14 = file_exists($rootDir . '/UC14_CapSBD_PhanPhongThi/public/index.php');
    $hasUc16 = file_exists($rootDir . '/UC16_ThucHienXetTuyen/public/index.php');
    $hasUc19 = file_exists($rootDir . '/UC19_QuanLyPhach/public/uc19.php');
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">10</div>
        <div>
            <b>QLTS LỚP 10</b>
            <small>Hệ thống tuyển sinh Sở GD&ĐT</small>
        </div>
    </div>

    <div class="nav-caption">NGHIỆP VỤ CỦA TÔI</div>
    <a href="uc09.php" class="nav-link <?= $activeKey === 'uc09' ? 'active' : '' ?>">
        📊 Thống kê tuyển sinh (UC09)
    </a>

    <?php if ($hasUc14 || $hasUc16 || $hasUc19): ?>
    <div class="nav-caption" style="margin-top: 18px;">CÁC PHÂN HỆ KHÁC TRONG NHÓM</div>
    <?php if ($hasUc14): ?>
    <a href="../../UC14_CapSBD_PhanPhongThi/public/index.php" class="nav-link">
        🏫 Cấp SBD & Phân phòng (UC14)
    </a>
    <?php endif; ?>
    <?php if ($hasUc16): ?>
    <a href="../../UC16_ThucHienXetTuyen/public/index.php" class="nav-link">
        🎯 Xét tuyển nguyện vọng (UC16)
    </a>
    <?php endif; ?>
    <?php if ($hasUc19): ?>
    <a href="../../UC19_QuanLyPhach/public/uc19.php" class="nav-link">
        📝 Quản lý phách bài thi (UC19)
    </a>
    <?php endif; ?>
    <?php endif; ?>

    <div class="sidebar-bottom">
        <div class="avatar"><?= mb_substr(h($user['fullname'] ?? 'BC'), 0, 1) ?></div>
        <div>
            <b><?= h($user['fullname'] ?? 'Thái Bá Cường') ?></b>
            <small><?= h($user['role'] ?? 'Ban Tuyển Sinh THPT') ?></small>
        </div>
    </div>
</aside>
<?php
}
