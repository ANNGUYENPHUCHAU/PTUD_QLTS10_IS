<?php
/**
 * Sidebar điều hướng chung cho toàn bộ các phân hệ của dự án
 * @param string $activeKey 'uc09' | 'uc14' | 'uc16' | 'uc21' | 'uc22' | 'uc23'
 */
function renderSidebar(string $activeKey = ''): void {
    $user = $_SESSION['user'] ?? ['username' => 'canbo', 'fullname' => 'Thái Bá Cường', 'role' => 'Cán bộ / Quản trị viên'];
    $rootDir = dirname(__DIR__, 2);
    $publicDir = $rootDir . '/public';

    $hasUc09 = file_exists($publicDir . '/uc09.php');
    $hasUc14 = file_exists($publicDir . '/uc14.php');
    $hasUc16 = file_exists($rootDir . '/UC16_ThucHienXetTuyen/public/index.php');
    $hasUc21 = file_exists($publicDir . '/uc21.php');
    $hasUc22 = file_exists($publicDir . '/uc22.php');
    $hasUc23 = file_exists($publicDir . '/uc23.php');
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">10</div>
        <div>
            <b>QLTS LỚP 10</b>
            <small>Hệ thống tuyển sinh Sở GD&ĐT</small>
        </div>
    </div>

    <div class="nav-caption">NGHIỆP VỤ TUYỂN SINH</div>
    <?php if ($hasUc09): ?>
    <a href="uc09.php" class="nav-link <?= $activeKey === 'uc09' ? 'active' : '' ?>">
        📊 Thống kê tuyển sinh (UC09)
    </a>
    <?php endif; ?>
    <?php if ($hasUc14): ?>
    <a href="uc14.php" class="nav-link <?= $activeKey === 'uc14' ? 'active' : '' ?>">
        🏫 Cấp SBD & Phân phòng (UC14)
    </a>
    <?php endif; ?>
    <?php if ($hasUc16): ?>
    <a href="/UC16_ThucHienXetTuyen/public/index.php" class="nav-link <?= $activeKey === 'uc16' ? 'active' : '' ?>">
        🎯 Xét tuyển nguyện vọng (UC16)
    </a>
    <?php endif; ?>

    <?php if ($hasUc21 || $hasUc22): ?>
    <div class="nav-caption" style="margin-top: 18px;">QUẢN TRỊ HỆ THỐNG</div>
    <?php if ($hasUc21): ?>
    <a href="uc21.php" class="nav-link <?= $activeKey === 'uc21' ? 'active' : '' ?>">
        👥 Quản lý tài khoản (UC21)
    </a>
    <?php endif; ?>
    <?php if ($hasUc22): ?>
    <a href="uc22.php" class="nav-link <?= $activeKey === 'uc22' ? 'active' : '' ?>">
        ⚙️ Cấu hình & Giám sát (UC22)
    </a>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasUc23): ?>
    <div class="nav-caption" style="margin-top: 18px;">BẢO MẬT & XÁC THỰC</div>
    <a href="uc23.php" class="nav-link <?= $activeKey === 'uc23' ? 'active' : '' ?>">
        🔑 Quên mật khẩu (UC23)
    </a>
    <?php endif; ?>

    <div class="sidebar-bottom">
        <div class="avatar"><?= mb_substr(h($user['fullname'] ?? 'BC'), 0, 1) ?></div>
        <div>
            <b><?= h($user['fullname'] ?? 'Thái Bá Cường') ?></b>
            <small><?= h($user['role'] ?? 'Quản trị viên') ?></small>
        </div>
    </div>
</aside>
<?php
}
