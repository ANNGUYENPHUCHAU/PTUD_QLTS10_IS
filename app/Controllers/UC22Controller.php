<?php
declare(strict_types=1);

final class UC22Controller
{
    private UC22Model $model;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/Core/uc14-config.php';
        $db = connectDatabase($config);
        $this->model = new UC22Model($db);
    }

    public function run(): void
    {
        $tab = (string)($_GET['tab'] ?? 'config'); // 'config' | 'backup' | 'monitor'
        $adminUser = $_SESSION['user']['username'] ?? 'admin';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
                flash('Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.', 'error');
                header("Location: uc22.php?tab=$tab");
                exit;
            }

            $action = (string)($_POST['action'] ?? '');

            if ($action === 'save_config') {
                $configs = $_POST['configs'] ?? [];
                if (is_array($configs)) {
                    foreach ($configs as $k => $val) {
                        $this->model->updateConfiguration((string)$k, (string)$val, $adminUser);
                    }
                    flash('Đã lưu cấu hình các tham số hệ thống thành công!');
                }
                header('Location: uc22.php?tab=config');
                exit;
            }

            if ($action === 'create_backup') {
                $backupDir = dirname(__DIR__, 2) . '/database/backups';
                try {
                    $result = $this->model->createDatabaseBackup($backupDir, $adminUser);
                    flash("Đã tạo bản sao lưu dữ liệu thành công: {$result['filename']} ({$result['size']})");
                } catch (Throwable $e) {
                    flash("Lỗi sao lưu dữ liệu: " . $e->getMessage(), 'error');
                }
                header('Location: uc22.php?tab=backup');
                exit;
            }
        }

        // Data for tabs
        $configs = $this->model->getConfigurations();
        $backups = $this->model->getBackupHistory();

        $logType = (string)($_GET['log_type'] ?? 'all');
        $logKeyword = trim((string)($_GET['log_q'] ?? ''));
        $logs = $this->model->getSystemLogs($logType, $logKeyword);

        $notice = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require dirname(__DIR__) . '/Views/Partials/sidebar.php';
        require dirname(__DIR__) . '/Views/UC22/index.php';
    }
}
