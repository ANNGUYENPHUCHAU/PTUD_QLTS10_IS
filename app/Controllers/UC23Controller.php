<?php
declare(strict_types=1);

final class UC23Controller
{
    private UC23Model $model;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/Core/uc14-config.php';
        $db = connectDatabase($config);
        $this->model = new UC23Model($db);
    }

    public function run(): void
    {
        $step = (int)($_SESSION['forgot_step'] ?? 1);
        $verifiedUser = $_SESSION['forgot_user'] ?? null;
        $notice = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
                flash('Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.', 'error');
                header('Location: uc23.php');
                exit;
            }

            $action = (string)($_POST['action'] ?? '');

            // BƯỚC 1: XÁC MINH DANH TÍNH
            if ($action === 'verify') {
                $username = trim((string)($_POST['username'] ?? ''));
                $emailOrPhone = trim((string)($_POST['email_or_phone'] ?? ''));

                if ($username === '' || $emailOrPhone === '') {
                    flash('Vui lòng điền đầy đủ Tên đăng nhập và Email hoặc Số điện thoại xác minh.', 'error');
                    header('Location: uc23.php');
                    exit;
                }

                $user = $this->model->verifyAccount($username, $emailOrPhone);
                if (!$user) {
                    flash('Thông tin xác minh không khớp với dữ liệu tài khoản trong hệ thống.', 'error');
                    header('Location: uc23.php');
                    exit;
                }

                if ($user['trang_thai'] === 'DaKhoa') {
                    flash('Tài khoản này hiện đang bị khóa. Vui lòng liên hệ Quản trị viên để mở khóa.', 'error');
                    header('Location: uc23.php');
                    exit;
                }

                $_SESSION['forgot_step'] = 2;
                $_SESSION['forgot_user'] = $user;
                flash('Xác minh tài khoản thành công! Vui lòng thiết lập mật khẩu mới bên dưới.');
                header('Location: uc23.php');
                exit;
            }

            // BƯỚC 2: ĐẶT MẬT KHẨU MỚI
            if ($action === 'reset_password') {
                if (!$verifiedUser) {
                    flash('Phiên xác thực đã hết hạn, vui lòng thực hiện lại.', 'error');
                    $_SESSION['forgot_step'] = 1;
                    header('Location: uc23.php');
                    exit;
                }

                $newPassword = (string)($_POST['new_password'] ?? '');
                $confirmPassword = (string)($_POST['confirm_password'] ?? '');

                if (strlen($newPassword) < 6) {
                    flash('Mật khẩu mới phải có độ dài tối thiểu 6 ký tự.', 'error');
                    header('Location: uc23.php');
                    exit;
                }

                if ($newPassword !== $confirmPassword) {
                    flash('Hai lần nhập mật khẩu không trùng khớp.', 'error');
                    header('Location: uc23.php');
                    exit;
                }

                $this->model->resetPassword((int)$verifiedUser['ma_nguoi_dung'], $newPassword, $verifiedUser['ten_dang_nhap']);

                // Reset session state
                unset($_SESSION['forgot_step'], $_SESSION['forgot_user']);
                flash('Thiết lập mật khẩu mới thành công! Bạn có thể sử dụng mật khẩu mới để đăng nhập.', 'success');
                $_SESSION['forgot_step'] = 3; // Hoàn tất
                header('Location: uc23.php');
                exit;
            }

            if ($action === 'restart') {
                unset($_SESSION['forgot_step'], $_SESSION['forgot_user']);
                header('Location: uc23.php');
                exit;
            }
        }

        require dirname(__DIR__) . '/Views/Partials/sidebar.php';
        require dirname(__DIR__) . '/Views/UC23/index.php';
    }
}
