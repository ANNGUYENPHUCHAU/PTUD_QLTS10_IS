<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\AdmissionRound;
use App\Models\AdmissionRun;
use App\Models\School;
use App\Services\AdmissionException;
use App\Services\AdmissionService;
use PDO;
use Throwable;

final class AdmissionController extends Controller
{
    private AdmissionRound $rounds;
    private AdmissionRun $runs;
    private School $schools;
    private AdmissionService $service;

    public function __construct(PDO $db)
    {
        $this->rounds = new AdmissionRound($db);
        $this->runs = new AdmissionRun($db);
        $this->schools = new School($db);
        $this->service = new AdmissionService($db);
    }

    public function index(): void
    {
        $this->view('admission/index', [
            'title' => 'Thực hiện xét tuyển',
            'rounds' => $this->rounds->all(),
            'schools' => $this->schools->all(),
            'flash' => Flash::pull(),
        ]);
    }

    public function run(): never
    {
        $this->requireValidPost();
        $roundId = filter_input(INPUT_POST, 'round_id', FILTER_VALIDATE_INT);
        if (!$roundId) {
            Flash::set('error', 'Vui lòng chọn đợt xét tuyển hợp lệ.');
            $this->redirect('index.php');
        }

        try {
            $runId = $this->service->run((int) $roundId, $_SESSION['user_name']);
            Flash::set('success', 'Đã tính toán kết quả. Vui lòng kiểm tra trước khi xác nhận.');
            $this->redirect('index.php?action=results&run_id=' . $runId);
        } catch (AdmissionException $e) {
            Flash::set('error', 'Không thể xét tuyển: ' . implode(' ', $e->errors()));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Flash::set('error', 'Có lỗi khi thực hiện xét tuyển. Dữ liệu chưa bị thay đổi.');
        }
        $this->redirect('index.php');
    }

    public function results(): void
    {
        $runId = filter_input(INPUT_GET, 'run_id', FILTER_VALIDATE_INT);
        $run = $runId ? $this->runs->find((int) $runId) : false;
        if (!$run) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Không tìm thấy kết quả', 'flash' => null]);
            return;
        }

        $this->view('admission/results', [
            'title' => 'Kết quả xét tuyển',
            'run' => $run,
            'summary' => $this->runs->summary((int) $runId),
            'flash' => Flash::pull(),
        ]);
    }

    public function school(): void
    {
        $runId = filter_input(INPUT_GET, 'run_id', FILTER_VALIDATE_INT);
        $schoolId = filter_input(INPUT_GET, 'school_id', FILTER_VALIDATE_INT);
        $run = $runId ? $this->runs->find((int) $runId) : false;
        $school = $schoolId ? $this->schools->find((int) $schoolId) : false;
        if (!$run || !$school) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Không tìm thấy dữ liệu', 'flash' => null]);
            return;
        }

        $this->view('admission/school', [
            'title' => 'Chi tiết kết quả trường',
            'run' => $run,
            'school' => $school,
            'results' => $this->runs->schoolResults((int) $runId, (int) $schoolId),
            'flash' => null,
        ]);
    }

    public function confirm(): never
    {
        $this->requireValidPost();
        $runId = filter_input(INPUT_POST, 'run_id', FILTER_VALIDATE_INT);
        if (!$runId) {
            Flash::set('error', 'Kết quả xét tuyển không hợp lệ.');
            $this->redirect('index.php');
        }

        try {
            $this->service->confirm((int) $runId);
            Flash::set('success', 'Xét tuyển thành công. Kết quả đã được lưu và xác nhận.');
        } catch (AdmissionException $e) {
            Flash::set('error', 'Lưu kết quả xét tuyển không thành công. ' . implode(' ', $e->errors()));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Flash::set('error', 'Lưu kết quả xét tuyển không thành công.');
        }
        $this->redirect('index.php?action=results&run_id=' . (int) $runId);
    }

    private function requireValidPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Phiên làm việc không hợp lệ. Vui lòng tải lại trang.');
        }
    }
}

