<?php
declare(strict_types=1);

final class UC14Controller
{
    private const DEMO_USER = 'canbo';
    // Hash lấy từ ứng dụng demo cũ. Không dùng đăng nhập demo khi triển khai thật.
    private const DEMO_PASSWORD_HASH = '$2y$12$ZPjqOMJWzeODOmIqZkdG8evRvgquftyegGjn4zqgPt/ElRJ5C6PaS';

    public function run(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        $notice = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        if (empty($_SESSION['signed_in'])) {
            require dirname(__DIR__) . '/Views/UC14/login.php';
            return;
        }

        try {
            $db = connectDatabase(require dirname(__DIR__) . '/Core/uc14-config.php');
            $model = new UC14Model($db);
            $source = $model->source();
        } catch (Throwable $e) {
            error_log('UC14 initial database error: ' . $e->getMessage());
            http_response_code(503);
            require dirname(__DIR__) . '/Views/UC14/error.php';
            return;
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
        foreach ($source['rooms'] as $room) {
            $roomsByPoint[$room['ma_diem_thi']][] = $room;
        }
        foreach ($roomsByPoint as &$rooms) {
            usort($rooms, fn($a, $b) => strnatcasecmp($a['so_phong'], $b['so_phong']));
        }
        unset($rooms);
        $totalCapacity = array_sum(array_map(fn($r) => (int)$r['suc_chua'], $source['rooms']));
        usort($items, static function($a, $b): int {
            return strcmp($a['ma_diem_thi'], $b['ma_diem_thi'])
                ?: strcmp($a['so_bao_danh'], $b['so_bao_danh']);
        });
        require dirname(__DIR__) . '/Views/UC14/index.php';
    }

    private function handlePost(): never
    {
        try {
            if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
                throw new DomainException('Phiên làm việc không hợp lệ. Hãy tải lại trang.');
            }
            $action = (string)($_POST['action'] ?? '');
            if ($action === 'login') {
                $username = trim((string)($_POST['username'] ?? ''));
                $password = (string)($_POST['password'] ?? '');
                if (!hash_equals(self::DEMO_USER, $username)
                    || !password_verify($password, self::DEMO_PASSWORD_HASH)) {
                    throw new DomainException('Tài khoản hoặc mật khẩu demo không đúng.');
                }
                session_regenerate_id(true);
                $_SESSION['signed_in'] = true;
                flash('Đăng nhập thành công.');
                backUC14();
            }
            if (empty($_SESSION['signed_in'])) {
                throw new DomainException('Bạn phải đăng nhập trước.');
            }
            if ($action === 'logout') {
                unset($_SESSION['signed_in'], $_SESSION['draft']);
                session_regenerate_id(true);
                flash('Đã đăng xuất.');
                backUC14();
            }
            $db = connectDatabase(require dirname(__DIR__) . '/Core/uc14-config.php');
            $model = new UC14Model($db);
            $source = $model->source();
            if ($action === 'generate') {
                $_SESSION['draft'] = UC14Service::buildDraft($source);
                flash('Đã tạo bản xem trước. Chưa có dữ liệu nào được lưu vào MySQL.');
            } elseif ($action === 'move') {
                if (empty($_SESSION['draft'])) {
                    throw new DomainException('Bạn cần tạo bản xem trước trước khi điều chỉnh.');
                }
                $_SESSION['draft'] = UC14Service::changeRoom(
                    $_SESSION['draft'], $source,
                    (string)($_POST['student_id'] ?? ''),
                    (string)($_POST['room_id'] ?? '')
                );
                flash('Đã điều chỉnh phòng thi trong bản tạm. Số báo danh được giữ nguyên.');
            } elseif ($action === 'confirm') {
                if (empty($_SESSION['draft'])) {
                    throw new DomainException('Chưa có dữ liệu xem trước để xác nhận.');
                }
                $model->confirm($_SESSION['draft']);
                unset($_SESSION['draft']);
                flash('Cấp số báo danh và phân phòng thi thành công. Kết quả đã được lưu vào MySQL.');
            } elseif ($action === 'reset') {
                if (($_POST['confirm_reset'] ?? '') !== 'RESET_DEMO') {
                    throw new DomainException('Chưa xác nhận đặt lại dữ liệu demo.');
                }
                $model->resetDemo();
                unset($_SESSION['draft']);
                flash('Đã đặt lại dữ liệu demo. Có thể thực hiện UC14 lần nữa.');
            } else {
                throw new DomainException('Thao tác không được hỗ trợ.');
            }
        } catch (DomainException $e) {
            flash($e->getMessage(), 'error');
        } catch (Throwable $e) {
            error_log('UC14 failure: ' . $e->getMessage());
            flash('Lưu kết quả không thành công hoặc hệ thống gặp lỗi. Kiểm tra MySQL, cấu hình và dữ liệu.', 'error');
        }
        backUC14();
    }
}
