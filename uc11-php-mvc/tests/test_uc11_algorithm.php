<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Ho_Chi_Minh');
require dirname(__DIR__) . '/app/Models/UC11Model.php';
require dirname(__DIR__) . '/app/Services/UC11Service.php';
if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "Cần bật pdo_sqlite trong php.ini.\n");
    exit(1);
}
$passed = 0;
function check(bool $condition, string $name): void {
    global $passed;
    if (!$condition) { throw new RuntimeException("FAIL: " . $name); }
    $passed++;
    echo "PASS: $name\n";
}
function denied(callable $work, string $name): void {
    try { $work(); } catch (DomainException $error) { check(true, $name); return; }
    throw new RuntimeException("FAIL: " . $name);
}
function fixture(): array {
    $db = new PDO('sqlite::memory:');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return [$db, new UC11Service(new UC11Model($db))];
}
function data(): array {
    return ['name'=>'Đợt 1', 'opens'=>'2027-06-01T08:00', 'closes'=>'2027-06-10T17:00',
        'selection'=>'2027-06-15T08:00', 'confirmation'=>'2027-06-20T17:00'];
}
try {
    [$db, $model] = fixture();
    $model->save(data(), 'canbo');
    check(count($model->all()) === 1 && count($model->logs()) === 1, 'Tạo đợt và ghi nhật ký');
    $row = $model->all()[0];
    $model->save(array_replace($row, ['name'=>'Đã sửa']), 'canbo');
    check($model->all()[0]['name'] === 'Đã sửa', 'Sửa và bỏ qua tự trùng');
    denied(fn() => $model->save($row, 'canbo'), 'Từ chối phiên bản cũ');
    denied(fn() => $model->save(data(), 'canbo'), 'Từ chối trùng lịch');
    denied(fn() => $model->save(array_replace(data(), ['name'=>'']), 'canbo'), 'Tên bắt buộc');
    denied(fn() => $model->save(array_replace(data(), ['opens'=>'2027-02-30T08:00']), 'canbo'), 'Ngày không tồn tại');
    denied(fn() => $model->save(array_replace(data(), ['closes'=>'2027-06-01T08:00']), 'canbo'), 'Hai mốc bằng nhau');
    denied(fn() => $model->save(array_replace(data(), ['selection'=>'2027-06-05T08:00']), 'canbo'), 'Mốc sai thứ tự');
    denied(fn() => $model->save(array_replace(data(), [
        'opens'=>'2027-06-20T17:00', 'closes'=>'2027-06-22T08:00',
        'selection'=>'2027-06-25T08:00','confirmation'=>'2027-06-30T17:00']), 'canbo'), 'Trùng điểm biên');
    denied(fn() => $model->save(array_replace(data(), [
        'opens'=>'2027-05-01T08:00','confirmation'=>'2027-07-01T08:00']), 'canbo'), 'Khoảng bao chứa');
    $row = $model->all()[0];
    denied(fn() => $model->changeStatus((int)$row['id'], (int)$row['version'], 'open', 'canbo',
        new DateTimeImmutable('2027-06-01 07:59')), 'Chưa đến giờ mở');
    denied(fn() => $model->changeStatus((int)$row['id'], (int)$row['version'], 'open', 'canbo',
        new DateTimeImmutable('2027-06-10 17:00')), 'Không mở tại giờ đóng');
    $model->changeStatus((int)$row['id'], (int)$row['version'], 'open', 'canbo',
        new DateTimeImmutable('2027-06-01 08:00'));
    $row = $model->all()[0];
    check($row['status'] === 'Đang mở', 'Mở đúng biên giờ mở');
    denied(fn() => $model->save($row, 'canbo'), 'Không sửa đợt đang mở');
    denied(fn() => $model->changeStatus((int)$row['id'], (int)$row['version'], 'close', 'canbo',
        new DateTimeImmutable('2027-06-10 16:59')), 'Không đóng sớm');
    $model->changeStatus((int)$row['id'], (int)$row['version'], 'close', 'canbo',
        new DateTimeImmutable('2027-06-10 17:00'));
    check($model->all()[0]['status'] === 'Đã đóng', 'Đóng đúng biên');
    [$db2, $model2] = fixture();
    $db2->exec("CREATE TRIGGER fail_audit BEFORE INSERT ON audit_logs BEGIN SELECT RAISE(ABORT,'test'); END");
    try { $model2->save(data(), 'canbo'); throw new RuntimeException('Không phát sinh lỗi'); }
    catch (PDOException $error) {
        check(count($model2->all()) === 0, 'Hoàn tác đợt khi ghi nhật ký lỗi');
    }
    $model->recordLogout('canbo');
    check($model->logs()[0]['action'] === 'Đăng xuất', 'Ghi nhật ký đăng xuất');
    echo "Tổng: $passed kiểm tra thành công.\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
