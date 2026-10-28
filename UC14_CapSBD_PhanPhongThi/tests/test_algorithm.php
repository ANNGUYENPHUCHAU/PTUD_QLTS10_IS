<?php
declare(strict_types=1);
require __DIR__ . '/../app/UC14Service.php';
function check(bool $ok, string $test): void {
    if (!$ok) { throw new RuntimeException('FAILED: ' . $test); }
    echo '[PASS] ' . $test . PHP_EOL;
}
$source = [
    'points' => [
        ['ma_diem_thi'=>'DT01','ten_diem_thi'=>'Diem thi A'],
        ['ma_diem_thi'=>'DT02','ten_diem_thi'=>'Diem thi B'],
    ],
    'rooms' => [
        ['ma_phong_thi'=>'P01','ma_diem_thi'=>'DT01','so_phong'=>'Phòng 1','suc_chua'=>2],
        ['ma_phong_thi'=>'P02','ma_diem_thi'=>'DT01','so_phong'=>'Phòng 2','suc_chua'=>2],
        ['ma_phong_thi'=>'P03','ma_diem_thi'=>'DT02','so_phong'=>'Phòng 1','suc_chua'=>1],
    ],
    'students' => [
        ['ma_hoc_sinh'=>'HS2','ma_diem_thi'=>'DT01','ma_truong'=>'TH1','ho_ten'=>'Trần Thị Bình','so_bao_danh'=>null],
        ['ma_hoc_sinh'=>'HS1','ma_diem_thi'=>'DT01','ma_truong'=>'TH1','ho_ten'=>'Nguyễn Văn An','so_bao_danh'=>null],
        ['ma_hoc_sinh'=>'HS3','ma_diem_thi'=>'DT01','ma_truong'=>'TH1','ho_ten'=>'Lê Thị Chi','so_bao_danh'=>null],
        ['ma_hoc_sinh'=>'HS4','ma_diem_thi'=>'DT02','ma_truong'=>'TH2','ho_ten'=>'Phạm Thị Duyên','so_bao_danh'=>null],
    ],
    'results' => [],
];
$draft = UC14Service::buildDraft($source);
check(count($draft['assignments']) === 4, 'Đã phân tất cả học sinh');
check($draft['assignments']['HS1']['so_bao_danh'] === '000001', 'Sắp tên A–Z, cấp SBD bắt đầu 000001');
check($draft['assignments']['HS2']['so_bao_danh'] === '000002', 'Sắp tên Bình sau An');
check($draft['assignments']['HS3']['ma_phong_thi'] === 'P02', 'Tự chuyển phòng khi phòng 1 đủ chỗ');
check(count(array_unique(array_column($draft['assignments'], 'so_bao_danh'))) === 4, 'Không trùng SBD');
check($draft['assignments']['HS4']['ma_phong_thi'] === 'P03', 'Không phân nhầm điểm thi');
UC14Service::validate($draft, $source);
check(true, 'Bản xem trước đạt các ràng buộc trước khi lưu');
$changed = UC14Service::changeRoom($draft, $source, 'HS1', 'P02');
check($changed['assignments']['HS1']['ma_phong_thi'] === 'P02' && $changed['assignments']['HS1']['so_bao_danh'] === '000001', 'Đổi phòng vẫn giữ nguyên SBD');
try {
    UC14Service::changeRoom($draft, $source, 'HS4', 'P01');
    check(false, 'Chặn chuyển sang điểm thi khác');
} catch (DomainException $e) { check(true, 'Chặn chuyển sang điểm thi khác'); }
try {
    UC14Service::changeRoom($draft, $source, 'HS3', 'P01');
    check(false, 'Chặn chuyển vào phòng đầy');
} catch (DomainException $e) { check(true, 'Chặn chuyển vào phòng đầy'); }
$source['rooms'][1]['suc_chua'] = 0;
try {
    UC14Service::buildDraft($source);
    check(false, 'Chặn điểm thi thiếu phòng');
} catch (DomainException $e) { check(true, 'Chặn điểm thi thiếu sức chứa'); }
echo 'Hoan thanh tat ca bai kiem thu thuat toan.' . PHP_EOL;
