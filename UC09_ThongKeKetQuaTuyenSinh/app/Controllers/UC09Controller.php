<?php
declare(strict_types=1);

final class UC09Controller
{
    private UC09Model $model;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/Core/uc09-config.php';
        $db = connectDatabase($config);
        $this->model = new UC09Model($db);
    }

    public function run(): void
    {
        $action = (string)($_GET['action'] ?? 'index');

        if ($action === 'export_csv') {
            $this->exportCsv();
            return;
        }

        $schools = $this->model->getHighSchools();
        $years = $this->model->getAdmissionYears();

        $selectedSchool = (string)($_GET['school'] ?? ($schools[0]['ma_truong_thpt'] ?? 'THPT01'));
        $selectedYear = (int)($_GET['year'] ?? ($years[0] ?? 2026));
        $selectedCriterion = (string)($_GET['criterion'] ?? 'all');

        $stats = $this->model->getStatistics($selectedSchool, $selectedYear);
        $candidates = $this->model->getFilteredCandidates($selectedSchool, $selectedYear, $selectedCriterion);

        $notice = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require dirname(__DIR__) . '/Views/Partials/sidebar.php';
        require dirname(__DIR__) . '/Views/UC09/index.php';
    }

    private function exportCsv(): void
    {
        $school = (string)($_GET['school'] ?? 'THPT01');
        $year = (int)($_GET['year'] ?? 2026);
        $criterion = (string)($_GET['criterion'] ?? 'all');

        $candidates = $this->model->getFilteredCandidates($school, $year, $criterion);
        $target = $this->model->getTargetInfo($school, $year);
        $schoolName = $target['ten_truong'] ?? $school;

        $filename = sprintf('thong_ke_tuyen_sinh_%s_%d.csv', $school, $year);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM cho Excel hiển thị đúng tiếng Việt có dấu
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['BÁO CÁO THỐNG KÊ KẾT QUẢ TUYỂN SINH LỚP 10']);
        fputcsv($output, ['Trường THPT:', $schoolName, 'Năm tuyển sinh:', $year]);
        fputcsv($output, []);
        fputcsv($output, ['STT', 'Mã thí sinh', 'Họ và tên', 'Trường THCS', 'Nguyện vọng', 'Điểm xét tuyển', 'Trạng thái hồ sơ', 'Kết quả tuyển sinh', 'Xác nhận nhập học']);

        $stt = 1;
        foreach ($candidates as $c) {
            fputcsv($output, [
                $stt++,
                $c['ma_hoc_sinh'],
                $c['ho_ten'] ?? 'Chưa cập nhật',
                $c['ten_thcs'] ?? 'N/A',
                'NV' . $c['nguyen_vong'],
                number_format((float)$c['diem_xet_tuyen'], 2),
                $c['trang_thai_ho_so'] === 'HopLe' ? 'Hợp lệ' : 'Không hợp lệ',
                $c['ket_qua_tuyen_sinh'] === 'TrungTuyen' ? 'Trúng tuyển' : ($c['ket_qua_tuyen_sinh'] === 'KhongTrungTuyen' ? 'Không trúng tuyển' : 'Đang xét'),
                $c['xac_nhan_nhap_hoc'] === 'DaXacNhan' ? 'Đã nhập học' : 'Chưa nhập học',
            ]);
        }

        fclose($output);
        exit;
    }
}
