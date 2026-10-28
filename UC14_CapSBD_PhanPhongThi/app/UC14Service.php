<?php
declare(strict_types=1);

/**
 * UC14 — Cấp số báo danh, phân phòng thi.
 * Những quy tắc bổ sung chưa có trong tài liệu được ghi trong README.md.
 */
final class UC14Service
{
    public static function source(PDO $pdo, bool $lock = false): array
    {
        $suffix = $lock ? ' FOR UPDATE' : '';
        $points = $pdo->query('SELECT ma_diem_thi, ten_diem_thi, dia_chi, suc_chua_toi_da, suc_chua_con_lai, tong_so_thi_sinh FROM diem_thi ORDER BY ma_diem_thi' . $suffix)->fetchAll();
        $rooms = $pdo->query('SELECT ma_phong_thi, ma_diem_thi, so_phong, suc_chua, so_luong_thi_sinh_hien_tai FROM phong_thi ORDER BY ma_diem_thi, ma_phong_thi' . $suffix)->fetchAll();
        $studentSql = <<<'SQL'
SELECT hs.ma_hoc_sinh, hs.ma_truong, hs.ma_diem_thi, hs.ho_ten,
       hs.so_bao_danh, hs.trang_thai_phan_phong, thcs.ten_truong
FROM hoc_sinh hs
JOIN truong_thcs thcs ON thcs.ma_truong = hs.ma_truong
JOIN ket_qua_phan_diem_thi k ON k.ma_truong = hs.ma_truong
    AND k.ma_diem_thi = hs.ma_diem_thi AND k.trang_thai_phan_diem = 'DaPhan'
ORDER BY hs.ma_diem_thi, hs.ma_hoc_sinh
SQL;
        // FOR UPDATE khóa các bản ghi nguồn trong lúc xác nhận kết quả.
        $students = $pdo->query($studentSql . $suffix)->fetchAll();
        $results = $pdo->query('SELECT ma_hoc_sinh, ma_diem_thi, ma_phong_thi, so_bao_danh FROM ket_qua_phan_phong_thi ORDER BY ma_hoc_sinh' . $suffix)->fetchAll();
        return compact('points', 'rooms', 'students', 'results');
    }

    public static function fingerprint(array $source): string
    {
        // Các tổng sức chứa/đã phân là số liệu tổng hợp: chỉ băm dữ liệu nguồn cần bất biến.
        $points = array_map(fn($p) => [$p['ma_diem_thi'], $p['ten_diem_thi']], $source['points']);
        $rooms = array_map(fn($r) => [$r['ma_phong_thi'], $r['ma_diem_thi'], $r['so_phong'], (int)$r['suc_chua']], $source['rooms']);
        $students = array_map(fn($s) => [$s['ma_hoc_sinh'], $s['ma_diem_thi'], $s['ma_truong'], $s['ho_ten'], $s['so_bao_danh']], $source['students']);
        return hash('sha256', json_encode([$points, $rooms, $students, $source['results']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public static function sortKey(string $name): string
    {
        // Sắp theo TÊN (từ cuối), sau đó HỌ/TÊN ĐỆM; bỏ dấu để so sánh A–Z.
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        $words = preg_split('/\s+/u', $name) ?: [$name];
        $given = (string)end($words);
        $normalize = static function (string $text): string {
            $text = str_replace(['Đ','đ'], ['D','d'], $text);
            if (function_exists('iconv')) {
                $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
            }
            return strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $text) ?? $text);
        };
        return $normalize($given) . '|' . $normalize($name);
    }

    public static function buildDraft(array $source): array
    {
        if ($source['results']) {
            throw new DomainException('Đã có kết quả phân phòng được lưu. Hãy xem kết quả hoặc sử dụng Đặt lại dữ liệu demo.');
        }
        if (!$source['students']) {
            throw new DomainException('Chưa có thí sinh đã phân điểm thi để cấp số báo danh.');
        }

        $studentsByPoint = [];
        foreach ($source['students'] as $student) {
            $studentsByPoint[$student['ma_diem_thi']][] = $student;
        }
        $roomsByPoint = [];
        foreach ($source['rooms'] as $room) {
            $roomsByPoint[$room['ma_diem_thi']][] = $room;
        }

        $assigned = [];
        $nextNumber = 1;
        foreach ($source['points'] as $point) {
            $pointId = $point['ma_diem_thi'];
            $students = $studentsByPoint[$pointId] ?? [];
            if (!$students) { continue; }
            $rooms = $roomsByPoint[$pointId] ?? [];
            usort($rooms, static fn($a, $b) => strnatcasecmp($a['so_phong'], $b['so_phong']));
            $capacity = array_sum(array_map(static fn($r) => (int)$r['suc_chua'], $rooms));
            if ($capacity < count($students)) {
                throw new DomainException('Không đủ phòng thi để phân thí sinh tại điểm thi "' . $point['ten_diem_thi'] . '" (cần ' . count($students) . ' chỗ, có ' . $capacity . ' chỗ). Không lưu kết quả.');
            }
            usort($students, static function ($a, $b): int {
                return strcmp(self::sortKey($a['ho_ten']), self::sortKey($b['ho_ten']))
                    ?: strcmp($a['ma_hoc_sinh'], $b['ma_hoc_sinh']);
            });
            $roomIndex = 0;
            $usedInRoom = 0;
            foreach ($students as $student) {
                while (isset($rooms[$roomIndex]) && $usedInRoom >= (int)$rooms[$roomIndex]['suc_chua']) {
                    $roomIndex++;
                    $usedInRoom = 0;
                }
                if (!isset($rooms[$roomIndex])) {
                    throw new DomainException('Không tìm thấy phòng đủ chỗ tại điểm thi ' . $pointId . '.');
                }
                $studentId = $student['ma_hoc_sinh'];
                $assigned[$studentId] = [
                    'ma_hoc_sinh' => $studentId,
                    'ma_diem_thi' => $pointId,
                    'ma_phong_thi' => $rooms[$roomIndex]['ma_phong_thi'],
                    'so_bao_danh' => sprintf('%06d', $nextNumber++),
                ];
                $usedInRoom++;
            }
        }
        if (count($assigned) !== count($source['students'])) {
            throw new DomainException('Có thí sinh không thuộc điểm thi hợp lệ. Vui lòng kiểm tra dữ liệu phân điểm thi.');
        }
        return [
            'fingerprint' => self::fingerprint($source),
            'assignments' => $assigned,
        ];
    }

    public static function changeRoom(array $draft, array $source, string $studentId, string $roomId): array
    {
        if (!hash_equals($draft['fingerprint'], self::fingerprint($source))) {
            throw new DomainException('Dữ liệu điểm thi đã thay đổi. Hãy tạo lại bản xem trước.');
        }
        if (!isset($draft['assignments'][$studentId])) {
            throw new DomainException('Thí sinh không có trong kết quả phân phòng tạm thời.');
        }
        $target = null;
        foreach ($source['rooms'] as $room) {
            if ($room['ma_phong_thi'] === $roomId) { $target = $room; break; }
        }
        if (!$target || $target['ma_diem_thi'] !== $draft['assignments'][$studentId]['ma_diem_thi']) {
            throw new DomainException('Chỉ được chuyển sang phòng thuộc cùng điểm thi.');
        }
        $occupancy = 0;
        foreach ($draft['assignments'] as $a) {
            if ($a['ma_phong_thi'] === $roomId && $a['ma_hoc_sinh'] !== $studentId) { $occupancy++; }
        }
        if ($occupancy >= (int)$target['suc_chua']) {
            throw new DomainException('Phòng đích đã đủ sức chứa. Không thể chuyển thí sinh.');
        }
        $draft['assignments'][$studentId]['ma_phong_thi'] = $roomId;
        return $draft;
    }

    public static function validate(array $draft, array $source): void
    {
        if ($source['results']) {
            throw new DomainException('Đã tồn tại kết quả được lưu. Không thể ghi đè.');
        }
        if (!hash_equals($draft['fingerprint'], self::fingerprint($source))) {
            throw new DomainException('Dữ liệu đã thay đổi sau khi xem trước. Hãy tạo lại để tránh phân phòng sai.');
        }
        $students = array_column($source['students'], null, 'ma_hoc_sinh');
        $rooms = array_column($source['rooms'], null, 'ma_phong_thi');
        $assignments = $draft['assignments'];
        if (count($assignments) !== count($students)) {
            throw new DomainException('Số lượng phân phòng không khớp danh sách thí sinh.');
        }
        $occupancy = [];
        $sbdSet = [];
        foreach ($assignments as $studentId => $a) {
            if (!isset($students[$studentId], $rooms[$a['ma_phong_thi']])) {
                throw new DomainException('Dữ liệu thí sinh hoặc phòng thi không hợp lệ.');
            }
            $roomId = $a['ma_phong_thi'];
            if ($students[$studentId]['ma_diem_thi'] !== $rooms[$roomId]['ma_diem_thi']) {
                throw new DomainException('Thí sinh bị phân sang điểm thi khác.');
            }
            if (isset($sbdSet[$a['so_bao_danh']])) {
                throw new DomainException('Trùng số báo danh.');
            }
            $sbdSet[$a['so_bao_danh']] = true;
            $occupancy[$roomId] = ($occupancy[$roomId] ?? 0) + 1;
            if ($occupancy[$roomId] > (int)$rooms[$roomId]['suc_chua']) {
                throw new DomainException('Phòng ' . $rooms[$roomId]['so_phong'] . ' vượt sức chứa.');
            }
        }
    }

    public static function confirm(PDO $pdo, array $draft): void
    {
        $pdo->beginTransaction();
        try {
            $source = self::source($pdo, true);
            self::validate($draft, $source);
            $insert = $pdo->prepare(<<<'SQL'
INSERT INTO ket_qua_phan_phong_thi
(ma_hoc_sinh, ma_diem_thi, ma_phong_thi, so_bao_danh, trang_thai_phan_phong)
VALUES (:student, :point, :room, :sbd, 'DaPhan')
SQL);
            $update = $pdo->prepare("UPDATE hoc_sinh SET so_bao_danh = :sbd, trang_thai_phan_phong = 'DaPhan' WHERE ma_hoc_sinh = :student");
            foreach ($draft['assignments'] as $a) {
                $insert->execute([
                    ':student' => $a['ma_hoc_sinh'],
                    ':point' => $a['ma_diem_thi'],
                    ':room' => $a['ma_phong_thi'],
                    ':sbd' => $a['so_bao_danh'],
                ]);
                $update->execute([':sbd' => $a['so_bao_danh'], ':student' => $a['ma_hoc_sinh']]);
            }
            $pdo->exec(<<<'SQL'
UPDATE phong_thi p SET so_luong_thi_sinh_hien_tai = (
 SELECT COUNT(*) FROM ket_qua_phan_phong_thi k WHERE k.ma_phong_thi = p.ma_phong_thi
)
SQL);
            $pdo->exec(<<<'SQL'
UPDATE diem_thi d SET
 suc_chua_toi_da = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi),
 tong_so_thi_sinh = (SELECT COUNT(*) FROM hoc_sinh h WHERE h.ma_diem_thi = d.ma_diem_thi),
 suc_chua_con_lai = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi) -
                    (SELECT COUNT(*) FROM ket_qua_phan_phong_thi k WHERE k.ma_diem_thi = d.ma_diem_thi)
SQL);
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $error;
        }
    }

    public static function resetDemo(PDO $pdo): void
    {
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM ket_qua_phan_phong_thi');
            $pdo->exec("UPDATE hoc_sinh SET so_bao_danh = NULL, trang_thai_phan_phong = 'ChuaPhan'");
            $pdo->exec('UPDATE phong_thi SET so_luong_thi_sinh_hien_tai = 0');
            $pdo->exec(<<<'SQL'
UPDATE diem_thi d SET
 suc_chua_toi_da = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi),
 tong_so_thi_sinh = (SELECT COUNT(*) FROM hoc_sinh h WHERE h.ma_diem_thi = d.ma_diem_thi),
 suc_chua_con_lai = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi)
SQL);
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $error;
        }
    }
}
