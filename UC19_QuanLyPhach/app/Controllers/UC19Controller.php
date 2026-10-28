<?php
declare(strict_types=1);

final class UC19Controller
{
    public function run(): void
    {
        $notice = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        try {
            $model = new UC19Model(connectDatabase(require dirname(__DIR__) . '/Core/uc19-config.php'));
            if ($_SERVER['REQUEST_METHOD'] === 'POST') $this->post($model);
            $user = $model->authorizedUser((int)($_SESSION['user_id'] ?? 0));
            if (!$user) {
                unset($_SESSION['user_id'], $_SESSION['drafts']);
                require dirname(__DIR__) . '/Views/UC19/login.php';
                return;
            }
            $catalog = $model->catalog();
            $exam = $this->choose($catalog, 'exam_id', 'exam');
            $examRows = array_values(array_filter($catalog, fn($r) => (int)$r['exam_id'] === $exam));
            $subject = $this->choose($examRows, 'subject_id', 'subject');
            $scope = 0;
            foreach ($examRows as $row) if ((int)$row['subject_id'] === $subject) $scope = (int)$row['scope_id'];
            $context = compact('exam','subject');
            $bags = $model->bags($scope);
            $bagId = $this->choose($bags, 'id', 'bag');
            $bag = $bagId ? $model->bag($bagId) : null;
            // Trang chấm không truy vấn danh sách có SBD, mã bài hoặc nguồn phòng thi.
            if (($_GET['view'] ?? '') === 'grading') {
                $numbers = $bag ? $model->grading($bagId, (int)$user['id']) : [];
                require dirname(__DIR__) . '/Views/UC19/grading.php';
                return;
            }
            $model->requireMapping((int)$user['id']);
            $entries = $bag ? $model->handover($bagId, (int)$user['id']) : [];
            $issues = $bag ? UC19Service::issues($bag, $entries) : [];
            $logs = $bag ? $model->logs($bagId, (int)$user['id']) : [];
            $draft = $_SESSION['drafts'][$bagId] ?? null;
            if ($draft && ($bag['status'] === 'created'
                || $draft['fingerprint'] !== UC19Service::fingerprint($bag, $entries))) {
                unset($_SESSION['drafts'][$bagId]);
                $draft = null;
                $notice ??= ['type'=>'error','message'=>'Dữ liệu túi chấm đã thay đổi. Bản xem trước cũ đã được loại bỏ.'];
            }
            require dirname(__DIR__) . '/Views/UC19/index.php';
        } catch (DomainException $error) {
            http_response_code(403);
            $errorMessage = $error->getMessage();
            require dirname(__DIR__) . '/Views/UC19/access-error.php';
        } catch (Throwable $error) {
            error_log('UC19: ' . $error->getMessage());
            http_response_code(503);
            require dirname(__DIR__) . '/Views/UC19/error.php';
        }
    }

    private function choose(array $rows, string $column, string $parameter): int
    {
        $allowed = array_map('intval', array_column($rows, $column));
        $requested = (int)($_GET[$parameter] ?? 0);
        return in_array($requested, $allowed, true) ? $requested : ($allowed[0] ?? 0);
    }

    private function post(UC19Model $model): never
    {
        $query = [];
        $action = (string)($_POST['action'] ?? '');
        try {
            if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
                throw new DomainException('Phiên làm việc không hợp lệ. Vui lòng tải lại trang.');
            }
            if ($action === 'logout') {
                $_SESSION = [];
                session_regenerate_id(true);
                $message = 'Đã đăng xuất.';
            } elseif ($action === 'login') {
                $user = $model->user(trim((string)($_POST['username'] ?? '')));
                if (!$user || !password_verify((string)($_POST['password'] ?? ''), $user['password_hash'])
                    || !$model->authorizedUser((int)$user['id'])) {
                    throw new DomainException('Tài khoản, mật khẩu không đúng hoặc bạn không có quyền quản lý phách.');
                }
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                unset($_SESSION['drafts'], $_SESSION['csrf']);
                $message = 'Đăng nhập thành công.';
            } else {
                $userId = (int)($_SESSION['user_id'] ?? 0);
                $model->requireMapping($userId);
                $id = (int)($_POST['bag_id'] ?? 0);
                $bag = $model->bag($id);
                $query = ['exam'=>$bag['exam_id'],'subject'=>$bag['subject_id'],'bag'=>$id];
                if ($action === 'generate') {
                    $_SESSION['drafts'][$id] = $model->createDraft($id, $userId);
                    $message = 'Đã tạo số phách dự kiến. Kiểm tra danh sách trước khi xác nhận lưu.';
                } else {
                    $draft = $_SESSION['drafts'][$id] ?? null;
                    if (!$draft || !hash_equals($draft['token'], (string)($_POST['draft_token'] ?? ''))) {
                        throw new DomainException('Bản xem trước đã thay đổi hoặc hết hạn. Vui lòng tải lại trang.');
                    }
                    if ($action === 'cancel') {
                        unset($_SESSION['drafts'][$id]);
                        $message = 'Đã hủy số phách dự kiến. Trạng thái túi chấm được giữ nguyên.';
                    } elseif ($action === 'regenerate') {
                        $_SESSION['drafts'][$id] = $model->regenerate($draft, (int)($_POST['handover_id'] ?? 0), $userId);
                        $message = 'Đã tạo lại số phách của bài thi được chọn.';
                    } elseif ($action === 'confirm') {
                        $model->save($draft, $userId);
                        unset($_SESSION['drafts'][$id]);
                        $message = 'Tạo và lưu số phách thành công';
                    } else {
                        throw new DomainException('Thao tác không hợp lệ.');
                    }
                }
            }
            $_SESSION['flash'] = ['type'=>'success','message'=>$message];
        } catch (DomainException $error) {
            $_SESSION['flash'] = ['type'=>'error','message'=>$error->getMessage()];
        } catch (Throwable $error) {
            error_log('UC19 POST: ' . $error->getMessage());
            $_SESSION['flash'] = ['type'=>'error','message'=>$action === 'confirm'
                ? 'Lưu dữ liệu phách không thành công' : 'Không thể thực hiện thao tác. Vui lòng thử lại.'];
        }
        header('Location: ' . uc19Url($query), true, 303);
        exit;
    }
}
