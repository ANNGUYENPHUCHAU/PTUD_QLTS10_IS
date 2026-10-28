<?php
declare(strict_types=1);

final class UC21Controller
{
    private UC21Model $model;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/Core/uc14-config.php';
        $db = connectDatabase($config);
        $this->model = new UC21Model($db);
    }

    public function run(): void
    {
        $action = (string)($_POST['action'] ?? $_GET['action'] ?? 'index');
        $adminUser = $_SESSION['user']['username'] ?? 'admin';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
                flash('Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.', 'error');
                header('Location: uc21.php');
                exit;
            }

            if ($action === 'create') {
                $username = trim((string)($_POST['username'] ?? ''));
                $password = trim((string)($_POST['password'] ?? ''));
                $fullname = trim((string)($_POST['fullname'] ?? ''));
                $email = trim((string)($_POST['email'] ?? ''));
                $phone = trim((string)($_POST['phone'] ?? ''));
                $role = (string)($_POST['role'] ?? 'CanBoSo');
                $perms = (string)($_POST['perms'] ?? '');

                if ($username === '' || $password === '' || $fullname === '' || $email === '') {
                    flash('Vui lòng điền đầy đủ các thông tin bắt buộc.', 'error');
                } elseif ($this->model->usernameExists($username)) {
                    flash("Tên đăng nhập '$username' đã tồn tại trong hệ thống.", 'error');
                } else {
                    $this->model->createUser([
                        'ten_dang_nhap' => $username,
                        'mat_khau' => $password,
                        'ho_ten' => $fullname,
                        'email' => $email,
                        'so_dien_thoai' => $phone,
                        'vai_tro' => $role,
                        'quyen_han' => $perms,
                        'trang_thai' => 'HoatDong',
                    ], $adminUser);
                    flash("Đã tạo tài khoản '$username' thành công!");
                }
                header('Location: uc21.php');
                exit;
            }

            if ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                $fullname = trim((string)($_POST['fullname'] ?? ''));
                $email = trim((string)($_POST['email'] ?? ''));
                $phone = trim((string)($_POST['phone'] ?? ''));
                $role = (string)($_POST['role'] ?? 'CanBoSo');
                $password = trim((string)($_POST['password'] ?? ''));
                $perms = (string)($_POST['perms'] ?? '');

                if ($id <= 0 || $fullname === '' || $email === '') {
                    flash('Thông tin cập nhật không hợp lệ.', 'error');
                } else {
                    $this->model->updateUser($id, [
                        'ho_ten' => $fullname,
                        'email' => $email,
                        'so_dien_thoai' => $phone,
                        'vai_tro' => $role,
                        'mat_khau' => $password,
                        'quyen_han' => $perms,
                    ], $adminUser);
                    flash('Cập nhật tài khoản và phân quyền thành công!');
                }
                header('Location: uc21.php');
                exit;
            }

            if ($action === 'toggle_status') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $newSt = $this->model->toggleStatus($id, $adminUser);
                    $msg = ($newSt === 'DaKhoa') ? 'Đã khóa tài khoản thành công.' : 'Đã mở khóa tài khoản thành công.';
                    flash($msg);
                }
                header('Location: uc21.php');
                exit;
            }
        }

        // View Logic
        $keyword = trim((string)($_GET['q'] ?? ''));
        $filterRole = (string)($_GET['role'] ?? 'all');
        $filterStatus = (string)($_GET['status'] ?? 'all');

        $users = $this->model->getUsers($keyword, $filterRole, $filterStatus);

        $editUser = null;
        if ($action === 'edit') {
            $id = (int)($_GET['id'] ?? 0);
            $editUser = $this->model->getUserById($id);
        }

        $notice = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require dirname(__DIR__) . '/Views/Partials/sidebar.php';
        require dirname(__DIR__) . '/Views/UC21/index.php';
    }
}
