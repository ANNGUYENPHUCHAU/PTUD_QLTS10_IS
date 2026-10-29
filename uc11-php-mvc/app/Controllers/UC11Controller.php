<?php
declare(strict_types=1);

final class UC11Controller
{
    public function __construct(private UC11Service $service) {}

    private function redirect(string $message = '', array $old = []): never
    {
        $_SESSION['flash'] = $message;
        $_SESSION['old'] = $old;
        header('Location: uc11.php');
        exit;
    }

    private function render(string $view, array $variables = []): void
    {
        extract($variables, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/UC11/layout.php';
    }

    public function handle(): void
    {
        $action = (string)($_GET['action'] ?? 'index');
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        if ($isPost) {
            $token = $_POST['csrf'] ?? '';
            if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
                http_response_code(403);
                $this->render('error', ['message' => 'Biểu mẫu hết hiệu lực. Hãy tải lại trang.']);
                return;
            }
            // Biểu mẫu chỉ nhận giá trị đơn, không nhận mảng lồng.
            foreach ($_POST as $value) {
                if (!is_string($value)) {
                    http_response_code(400);
                    $this->render('error', ['message' => 'Dữ liệu biểu mẫu không hợp lệ.']);
                    return;
                }
            }
            if ($action === 'login') {
                if (($_POST['username'] ?? '') === 'canbo' && ($_POST['password'] ?? '') === 'Demo@123') {
                    session_regenerate_id(true);
                    $_SESSION['user'] = 'canbo';
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                    $this->redirect('Đăng nhập thành công.');
                }
                $this->redirect('Tài khoản hoặc mật khẩu không đúng.');
            }
        }
        if (empty($_SESSION['user'])) {
            $message = $_SESSION['flash'] ?? '';
            unset($_SESSION['flash'], $_SESSION['old']);
            $this->render('login', ['message' => $message]);
            return;
        }
        if ($isPost && $action === 'logout') {
            try {
                $this->service->recordLogout($_SESSION['user']);
            } catch (Throwable $error) {
                error_log('Không ghi được nhật ký đăng xuất: ' . $error->getMessage());
            } finally {
                $_SESSION = [];
                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000, $params['path'],
                        $params['domain'], $params['secure'], $params['httponly']);
                }
                session_destroy();
            }
            header('Location: uc11.php');
            exit;
        }
        if ($isPost && in_array($action, ['save', 'open', 'close'], true)) {
            try {
                if ($action === 'save') {
                    $message = $this->service->save($_POST, $_SESSION['user']);
                } else {
                    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
                    $version = filter_var($_POST['version'] ?? '', FILTER_VALIDATE_INT);
                    if ($id === false || $id <= 0 || $version === false || $version <= 0) {
                        throw new DomainException('Mã đợt hoặc phiên bản không hợp lệ.');
                    }
                    $message = $this->service->changeStatus($id, $version, $action, $_SESSION['user']);
                }
                $this->redirect($message);
            } catch (DomainException $error) {
                $this->redirect($error->getMessage(), $action === 'save' ? $_POST : []);
            } catch (Throwable $error) {
                error_log($error->getMessage());
                $this->redirect('Lưu đợt tuyển sinh không thành công. Dữ liệu được giữ nguyên.', $action === 'save' ? $_POST : []);
            }
        }
        if ($isPost || !in_array($action, ['index', 'edit'], true)) {
            http_response_code(404);
            $this->render('error', ['message' => 'Không tìm thấy chức năng.']);
            return;
        }
        $message = $_SESSION['flash'] ?? '';
        $current = $_SESSION['old'] ?? [];
        unset($_SESSION['flash'], $_SESSION['old']);
        try {
            if ($action === 'edit') {
                $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
                $current = $id !== false ? $this->service->find($id) : null;
                if (!$current) {
                    $this->redirect('Đợt không tồn tại. Hãy tải lại danh sách.');
                }
            }
            $this->render('index', [
                'rounds' => $this->service->all(), 'logs' => $this->service->logs(),
                'current' => $current, 'message' => $message,
            ]);
        } catch (Throwable $error) {
            error_log($error->getMessage());
            $this->render('error', ['message' => 'Không thể tải dữ liệu đợt tuyển sinh.']);
        }
    }
}
