<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/UC14Service.php';

// Mẫu đăng nhập phục vụ DEMO trên máy cá nhân; không dùng làm hệ xác thực thật.
const DEMO_USER = 'canbo';
const DEMO_PASSWORD_HASH = '$2y$12$ZPjqOMJWzeODOmIqZkdG8evRvgquftyegGjn4zqgPt/ElRJ5C6PaS';

function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(24)); }
    return $_SESSION['csrf'];
}
function hiddenCsrf(): void {
    echo '<input type="hidden" name="csrf" value="' . h(csrf()) . '">';
}
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function back(): never {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
            throw new DomainException('Phiên làm việc không hợp lệ. Hãy tải lại trang.');
        }
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'login') {
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            if (!hash_equals(DEMO_USER, $username) || !password_verify($password, DEMO_PASSWORD_HASH)) {
                throw new DomainException('Tài khoản hoặc mật khẩu demo không đúng.');
            }
            session_regenerate_id(true);
            $_SESSION['signed_in'] = true;
            flash('Đăng nhập thành công.');
            back();
        }
        if (empty($_SESSION['signed_in'])) { throw new DomainException('Bạn phải đăng nhập trước.'); }
        if ($action === 'logout') {
            unset($_SESSION['signed_in'], $_SESSION['draft']);
            session_regenerate_id(true);
            flash('Đã đăng xuất.');
            back();
        }
        $db = connectDatabase(require __DIR__ . '/../app/config.php');
        $source = UC14Service::source($db);
        if ($action === 'generate') {
            $_SESSION['draft'] = UC14Service::buildDraft($source);
            flash('Đã tạo bản xem trước. Chưa có dữ liệu nào được lưu vào MySQL.');
        } elseif ($action === 'move') {
            if (empty($_SESSION['draft'])) { throw new DomainException('Bạn cần tạo bản xem trước trước khi điều chỉnh.'); }
            $_SESSION['draft'] = UC14Service::changeRoom(
                $_SESSION['draft'], $source, (string)($_POST['student_id'] ?? ''), (string)($_POST['room_id'] ?? '')
            );
            flash('Đã điều chỉnh phòng thi trong bản tạm. Số báo danh được giữ nguyên.');
        } elseif ($action === 'confirm') {
            if (empty($_SESSION['draft'])) { throw new DomainException('Chưa có dữ liệu xem trước để xác nhận.'); }
            UC14Service::confirm($db, $_SESSION['draft']);
            unset($_SESSION['draft']);
            flash('Cấp số báo danh và phân phòng thi thành công. Kết quả đã được lưu vào MySQL.');
        } elseif ($action === 'reset') {
            // CHỈ PHỤC VỤ DEMO: thao tác này xóa kết quả phân phòng đã lưu.
            if (($_POST['confirm_reset'] ?? '') !== 'RESET_DEMO') {
                throw new DomainException('Chưa xác nhận đặt lại dữ liệu demo.');
            }
            UC14Service::resetDemo($db);
            unset($_SESSION['draft']);
            flash('Đã đặt lại dữ liệu demo. Có thể thực hiện UC14 lần nữa.');
        } else {
            throw new DomainException('Thao tác không được hỗ trợ.');
        }
    } catch (DomainException $e) {
        flash($e->getMessage(), 'error');
    } catch (Throwable $e) {
        error_log('UC14 failure: ' . $e->getMessage());
        flash('Lưu kết quả cấp số báo danh và phân phòng thi không thành công hoặc hệ thống gặp lỗi. Kiểm tra MySQL, cấu hình và dữ liệu.', 'error');
    }
    back();
}

$notice = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$isLoggedIn = !empty($_SESSION['signed_in']);
if (!$isLoggedIn):
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập | UC14</title><link rel="stylesheet" href="assets/style.css"></head>
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
<?php exit; endif;

try {
    $db = connectDatabase(require __DIR__ . '/../app/config.php');
    $source = UC14Service::source($db);
} catch (Throwable $e) {
    error_log('UC14 initial database error: ' . $e->getMessage());
    http_response_code(503);
    ?><!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="assets/style.css"><title>Lỗi kết nối</title></head><body class="login-page"><main class="login-card"><div class="brand-logo large">!</div><h1>Không thể kết nối MySQL</h1><p>Hãy bật <b>Apache + MySQL</b> trong XAMPP, sau đó nhập file <code>database/uc14_demo.sql</code> bằng phpMyAdmin.</p><p>Nếu MySQL chạy cổng 3307, tạo <code>app/config.local.php</code> để đổi <code>db_port</code> (xem README).</p><a class="btn btn-primary" href="index.php">Thử lại</a></main></body></html><?php exit;
}
$roomsById = array_column($source['rooms'], null, 'ma_phong_thi');
$pointsById = array_column($source['points'], null, 'ma_diem_thi');
$studentsById = array_column($source['students'], null, 'ma_hoc_sinh');
$draft = $_SESSION['draft'] ?? null;
$hasCommitted = count($source['results']) > 0;
$items = $draft ? array_values($draft['assignments']) : $source['results'];
$roomCounts = [];
foreach ($items as $assignment) {
    $roomId = $assignment['ma_phong_thi'];
    $roomCounts[$roomId] = ($roomCounts[$roomId] ?? 0) + 1;
}
$studentCountByPoint = [];
foreach ($source['students'] as $student) {
    $pointId = $student['ma_diem_thi'];
    $studentCountByPoint[$pointId] = ($studentCountByPoint[$pointId] ?? 0) + 1;
}
$roomsByPoint = [];
foreach ($source['rooms'] as $room) { $roomsByPoint[$room['ma_diem_thi']][] = $room; }
foreach ($roomsByPoint as &$rooms) { usort($rooms, fn($a, $b) => strnatcasecmp($a['so_phong'], $b['so_phong'])); }
unset($rooms);
$totalCapacity = array_sum(array_map(fn($r) => (int)$r['suc_chua'], $source['rooms']));
usort($items, function($a, $b) use ($studentsById): int {
    return strcmp($a['ma_diem_thi'], $b['ma_diem_thi']) ?: strcmp($a['so_bao_danh'], $b['so_bao_danh']);
});
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>UC14 | Cấp số báo danh và phân phòng thi</title><link rel="stylesheet" href="assets/style.css"></head>
<body>
<div class="app-shell">
  <aside class="sidebar">
    <div class="brand"><div class="brand-logo">10</div><div><b>TUYỂN SINH</b><small>Hệ thống quản lý THPT</small></div></div>
    <div class="nav-caption">NGHIỆP VỤ TUYỂN SINH</div>
    <a class="nav-link active" href="#tong-quan">◫ &nbsp; Tổng quan</a>
    <a class="nav-link" href="#diem-thi">▤ &nbsp; Danh sách điểm thi</a>
    <a class="nav-link" href="#ket-qua">▦ &nbsp; Kết quả phân phòng</a>
    <div class="sidebar-bottom"><div class="avatar">CB</div><div><b>Cán bộ tuyển sinh</b><small>Sở Giáo dục & Đào tạo</small></div></div>
  </aside>
  <div class="main-area">
    <header class="topbar"><div><span class="crumb">Quản lý tuyển sinh</span><span class="slash"> / </span><strong>Cấp SBD & phân phòng</strong></div><form method="post"><?php hiddenCsrf(); ?><input type="hidden" name="action" value="logout"><button class="btn btn-ghost">Đăng xuất</button></form></header>
    <main class="content" id="tong-quan">
      <div class="headline"><div><div class="eyebrow">CHỨC NĂNG UC14 · PHÒNG QUẢN LÝ TUYỂN SINH</div><h1>Cấp số báo danh & phân phòng thi</h1><p class="muted">Tự động sắp xếp thí sinh, phân bổ phòng theo sức chứa và xác nhận lưu kết quả.</p></div><span class="pill <?= $hasCommitted ? 'pill-green' : ($draft ? 'pill-amber' : 'pill-blue') ?>"><?= $hasCommitted ? 'Đã lưu kết quả' : ($draft ? 'Bản xem trước' : 'Sẵn sàng xử lý') ?></span></div>
      <?php if ($notice): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-error' : 'alert-success' ?>"><strong><?= $notice['type'] === 'error' ? 'Thông báo lỗi' : 'Thành công' ?>:</strong> <?= h($notice['message']) ?></div><?php endif; ?>
      <section class="stat-grid">
        <div class="stat-card"><span>Điểm thi</span><strong><?= count($source['points']) ?></strong><small>Đã có kết quả phân điểm</small><div class="stat-icon">⌂</div></div>
        <div class="stat-card"><span>Tổng thí sinh</span><strong><?= count($source['students']) ?></strong><small>Đủ điều kiện cấp SBD</small><div class="stat-icon">♧</div></div>
        <div class="stat-card"><span>Phòng thi</span><strong><?= count($source['rooms']) ?></strong><small>Phòng thuộc các điểm thi</small><div class="stat-icon">▤</div></div>
        <div class="stat-card"><span>Tổng sức chứa</span><strong><?= $totalCapacity ?></strong><small><?= max(0, $totalCapacity - count($source['students'])) ?> chỗ còn dư trên toàn bộ điểm thi</small><div class="stat-icon">◫</div></div>
      </section>
      <section class="panel" id="diem-thi"><div class="panel-head"><div><h2>Danh sách điểm thi</h2><p>Tổng hợp thí sinh và số lượng phòng theo từng điểm thi.</p></div><span class="subtle">Dữ liệu đầu vào</span></div>
        <div class="table-scroll"><table><thead><tr><th>Mã</th><th>Điểm thi</th><th>Số thí sinh</th><th>Phòng thi</th><th>Sức chứa</th><th>Trạng thái</th></tr></thead><tbody>
          <?php foreach ($source['points'] as $point): $pointId=$point['ma_diem_thi']; $pointRooms=$roomsByPoint[$pointId] ?? []; $pointCapacity=array_sum(array_map(fn($r)=>(int)$r['suc_chua'],$pointRooms)); $pointStudentCount=$studentCountByPoint[$pointId] ?? 0; ?>
          <tr><td><span class="code-chip"><?= h($pointId) ?></span></td><td><strong><?= h($point['ten_diem_thi']) ?></strong><small class="table-subtext"><?= h($point['dia_chi']) ?></small></td><td><?= $pointStudentCount ?></td><td><?= count($pointRooms) ?></td><td><?= $pointCapacity ?> chỗ</td><td><span class="pill <?= $pointCapacity >= $pointStudentCount ? 'pill-green' : 'pill-red' ?>"><?= $pointCapacity >= $pointStudentCount ? 'Đủ chỗ' : 'Thiếu chỗ' ?></span></td></tr>
          <?php endforeach; ?></tbody></table></div>
      </section>
      <section class="panel operation-panel"><div class="panel-head"><div><h2>Thực hiện cấp số báo danh</h2><p>Sắp theo tên A–Z, cấp số thứ tự toàn hệ thống, phân vào phòng từ số nhỏ đến lớn.</p></div></div>
        <div class="operation-actions">
          <?php if (!$hasCommitted): ?>
            <form method="post"><?php hiddenCsrf(); ?><input type="hidden" name="action" value="generate"><button type="submit" class="btn btn-primary">↻ &nbsp; <?= $draft ? 'Tạo lại kết quả tự động' : 'Cấp SBD & phân phòng tự động' ?></button></form>
          <?php else: ?><span class="status-message">✓ &nbsp; Kết quả đã được lưu. Bạn có thể xem bên dưới.</span><?php endif; ?>
          <?php if ($hasCommitted): ?><form method="post" onsubmit="return confirm('Chỉ dùng cho DEMO. XÓA TOÀN BỘ kết quả phân phòng đã lưu để chạy lại?');"><?php hiddenCsrf(); ?><input type="hidden" name="action" value="reset"><input type="hidden" name="confirm_reset" value="RESET_DEMO"><button type="submit" class="btn btn-outline-danger">Đặt lại dữ liệu demo</button></form><?php endif; ?>
        </div>
        <div class="info-bar"><span class="info-mark">i</span><span><?= $draft ? 'Đây là bản phân phòng tạm thời. Bạn có thể chỉnh phòng trước khi bấm Xác nhận & lưu.' : ($hasCommitted ? 'Các thay đổi đã được ghi vào cơ sở dữ liệu MySQL.' : 'Nhấn nút để tạo bản xem trước. Chưa ghi dữ liệu vào MySQL cho đến khi bạn xác nhận.') ?></span></div>
      </section>
      <section class="panel" id="ket-qua"><div class="panel-head"><div><h2><?= $draft ? 'Xem trước kết quả phân phòng' : 'Kết quả cấp SBD & phân phòng' ?></h2><p><?= count($items) ?> thí sinh <?= $draft ? 'đang có kết quả tạm thời' : ($hasCommitted ? 'đã được lưu' : 'chưa được phân phòng') ?>.</p></div>
        <?php if ($items): ?><label class="filter-label">Lọc điểm thi <select id="site-filter"><option value="all">Tất cả điểm thi</option><?php foreach ($source['points'] as $p): ?><option value="<?= h($p['ma_diem_thi']) ?>"><?= h($p['ten_diem_thi']) ?></option><?php endforeach; ?></select></label><?php endif; ?></div>
        <?php if (!$items): ?><div class="empty"><span class="empty-icon">▧</span><h3>Chưa có kết quả phân phòng</h3><p>Hãy chọn “Cấp SBD & phân phòng tự động” để xem kết quả tại đây.</p></div>
        <?php else: ?><div class="table-scroll"><table><thead><tr><th>Số báo danh</th><th>Mã học sinh</th><th>Họ tên thí sinh</th><th>Trường THCS</th><th>Điểm thi</th><th>Phòng thi<?= $draft ? ' / Điều chỉnh' : '' ?></th></tr></thead><tbody>
          <?php foreach ($items as $item): $student=$studentsById[$item['ma_hoc_sinh']] ?? null; $point=$pointsById[$item['ma_diem_thi']] ?? null; $room=$roomsById[$item['ma_phong_thi']] ?? null; if (!$student) continue; ?>
          <tr data-point="<?= h($item['ma_diem_thi']) ?>"><td><span class="sbd"><?= h($item['so_bao_danh']) ?></span></td><td><?= h($student['ma_hoc_sinh']) ?></td><td><strong><?= h($student['ho_ten']) ?></strong></td><td><?= h($student['ten_truong']) ?></td><td><?= h($point['ten_diem_thi'] ?? $item['ma_diem_thi']) ?></td><td>
           <?php if ($draft): ?><form method="post" class="move-form"><?php hiddenCsrf(); ?><input type="hidden" name="action" value="move"><input type="hidden" name="student_id" value="<?= h($item['ma_hoc_sinh']) ?>"><select name="room_id" aria-label="Phòng của <?= h($student['ho_ten']) ?>"><?php foreach (($roomsByPoint[$item['ma_diem_thi']] ?? []) as $opt): $used=$roomCounts[$opt['ma_phong_thi']] ?? 0; ?><option value="<?= h($opt['ma_phong_thi']) ?>" <?= $item['ma_phong_thi'] === $opt['ma_phong_thi'] ? 'selected' : '' ?>><?= h($opt['so_phong']) ?> (<?= $used ?>/<?= (int)$opt['suc_chua'] ?>)</option><?php endforeach; ?></select><button class="btn btn-small btn-outline" title="Lưu chỉnh sửa phòng của thí sinh">Đổi</button></form>
           <?php else: ?><strong><?= h($room['so_phong'] ?? $item['ma_phong_thi']) ?></strong><?php endif; ?>
          </td></tr><?php endforeach; ?></tbody></table></div>
          <?php if ($draft): ?><div class="confirm-bar"><div><strong>Đã kiểm tra kết quả?</strong><small>Bấm xác nhận để lưu toàn bộ vào cơ sở dữ liệu.</small></div><form method="post" onsubmit="return confirm('Xác nhận lưu toàn bộ số báo danh và kết quả phân phòng thi vào MySQL?');"><?php hiddenCsrf(); ?><input type="hidden" name="action" value="confirm"><button class="btn btn-primary">✓ &nbsp; Xác nhận & lưu kết quả</button></form></div><?php endif; ?>
        <?php endif; ?>
      </section>
      <p class="footer-note">UC14 · Bản demo học phần Phát triển ứng dụng · Dữ liệu trong hệ thống là dữ liệu mô phỏng</p>
    </main>
  </div>
</div>
<script>
const filter = document.getElementById('site-filter');
if (filter) filter.addEventListener('change', () => {
  document.querySelectorAll('[data-point]').forEach(row => {
    row.hidden = filter.value !== 'all' && row.dataset.point !== filter.value;
  });
});
</script>
</body></html>
