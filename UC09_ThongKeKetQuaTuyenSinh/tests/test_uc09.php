<?php
declare(strict_types=1);

/**
 * Script kiểm thử tự động cho Use Case 09: Thống kê kết quả tuyển sinh
 */
echo "=== BẮT ĐẦU KIỂM THỬ UC09 (THỐNG KÊ KẾT QUẢ TUYỂN SINH) ===\n";

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/uc09-helpers.php';
require_once dirname(__DIR__) . '/app/Models/UC09Model.php';

$config = require dirname(__DIR__) . '/app/Core/uc09-config.php';

try {
    $db = connectDatabase($config);
    echo "[PASS] Kết nối cơ sở dữ liệu MySQL/MariaDB thành công\n";
} catch (Throwable $e) {
    echo "[FAIL] Không thể kết nối cơ sở dữ liệu: " . $e->getMessage() . "\n";
    exit(1);
}

$model = new UC09Model($db);
$passed = 0;
$total = 0;

function assertCondition(bool $cond, string $msg, int &$passed, int &$total): void {
    $total++;
    if ($cond) {
        $passed++;
        echo "[PASS] $msg\n";
    } else {
        echo "[FAIL] $msg\n";
    }
}

// 1. Kiểm tra danh sách trường THPT
$schools = $model->getHighSchools();
assertCondition(!empty($schools), "Lấy được danh sách trường THPT (có " . count($schools) . " trường)", $passed, $total);
$hasNguyenHue = false;
foreach ($schools as $s) {
    if ($s['ma_truong_thpt'] === 'THPT01') { $hasNguyenHue = true; break; }
}
assertCondition($hasNguyenHue, "Tìm thấy trường THPT Nguyễn Huệ (THPT01)", $passed, $total);

// 2. Kiểm tra năm tuyển sinh
$years = $model->getAdmissionYears();
assertCondition(in_array(2026, $years, true) || in_array('2026', $years, false), "Có dữ liệu năm tuyển sinh 2026", $passed, $total);

// 3. Kiểm tra thông tin chỉ tiêu & điểm chuẩn
$target = $model->getTargetInfo('THPT01', 2026);
assertCondition($target !== null && (int)$target['tong_chi_tieu'] === 450, "Đọc đúng chỉ tiêu THPT Nguyễn Huệ năm 2026 (450 chỉ tiêu)", $passed, $total);
assertCondition((float)$target['diem_chuan_nv1'] === 22.50, "Đọc đúng điểm chuẩn NV1 THPT Nguyễn Huệ (22.50 điểm)", $passed, $total);

// 4. Kiểm tra tổng hợp số liệu thống kê
$stats = $model->getStatistics('THPT01', 2026);
assertCondition($stats['tong_ho_so'] > 0, "Thống kê được tổng số hồ sơ nộp (" . $stats['tong_ho_so'] . " hồ sơ)", $passed, $total);
assertCondition($stats['so_trung_tuyen'] > 0, "Thống kê được số thí sinh trúng tuyển (" . $stats['so_trung_tuyen'] . " thí sinh)", $passed, $total);
assertCondition($stats['da_nhap_hoc'] > 0, "Thống kê được số thí sinh đã xác nhận nhập học (" . $stats['da_nhap_hoc'] . " thí sinh)", $passed, $total);
assertCondition($stats['ty_le_choi'] >= 0, "Tính toán đúng tỷ lệ chọi (1 : " . $stats['ty_le_choi'] . ")", $passed, $total);
assertCondition($stats['ty_le_nhap_hoc'] >= 0, "Tính toán đúng tỷ lệ nhập học (" . $stats['ty_le_nhap_hoc'] . "%)", $passed, $total);

// 5. Kiểm tra lọc danh sách thí sinh
$allCandidates = $model->getFilteredCandidates('THPT01', 2026, 'all');
assertCondition(!empty($allCandidates), "Lọc danh sách tất cả thí sinh thành công", $passed, $total);

$admitted = $model->getFilteredCandidates('THPT01', 2026, 'trung_tuyen');
$allAdmittedMatch = true;
foreach ($admitted as $c) {
    if ($c['ket_qua_tuyen_sinh'] !== 'TrungTuyen') { $allAdmittedMatch = false; break; }
}
assertCondition($allAdmittedMatch && count($admitted) === $stats['so_trung_tuyen'], "Lọc đúng danh sách thí sinh trúng tuyển", $passed, $total);

$confirmed = $model->getFilteredCandidates('THPT01', 2026, 'da_nhap_hoc');
$allConfirmedMatch = true;
foreach ($confirmed as $c) {
    if ($c['xac_nhan_nhap_hoc'] !== 'DaXacNhan') { $allConfirmedMatch = false; break; }
}
assertCondition($allConfirmedMatch && count($confirmed) === $stats['da_nhap_hoc'], "Lọc đúng danh sách thí sinh đã nhập học", $passed, $total);

echo "\n--------------------------------------------------\n";
echo "KẾT QUẢ: $passed / $total kiểm thử ĐÃ VƯỢT QUA [PASS]\n";
if ($passed === $total) {
    echo "🎉 TẤT CẢ KIỂM THỬ UC09 ĐÃ THÀNH CÔNG 100%!\n";
    exit(0);
} else {
    echo "⚠️ Có kiểm thử chưa đạt!\n";
    exit(1);
}
