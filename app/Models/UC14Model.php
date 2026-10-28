<?php
declare(strict_types=1);

/** Model UC14: mọi truy vấn SQL và thao tác lưu dữ liệu. */
final class UC14Model
{
    public function __construct(private PDO $db) {}

    // Truy vấn danh sách điểm thi (có thể khóa trong giao dịch).
    public function getExamSites(bool $lock = false): array
    {
        $sql = 'SELECT ma_diem_thi, ten_diem_thi, dia_chi, suc_chua_toi_da, suc_chua_con_lai, tong_so_thi_sinh
                FROM diem_thi ORDER BY ma_diem_thi';
        return $this->db->query($sql . ($lock ? ' FOR UPDATE' : ''))->fetchAll();
    }

    // Truy vấn phòng thi.
    public function getExamRooms(bool $lock = false): array
    {
        $sql = 'SELECT ma_phong_thi, ma_diem_thi, so_phong, suc_chua, so_luong_thi_sinh_hien_tai
                FROM phong_thi ORDER BY ma_diem_thi, ma_phong_thi';
        return $this->db->query($sql . ($lock ? ' FOR UPDATE' : ''))->fetchAll();
    }

    // Chỉ lấy học sinh thuộc trường đã được phân điểm thi.
    public function getEligibleStudents(bool $lock = false): array
    {
        $sql = <<<'SQL'
SELECT hs.ma_hoc_sinh, hs.ma_truong, hs.ma_diem_thi, hs.ho_ten,
       hs.so_bao_danh, hs.trang_thai_phan_phong, thcs.ten_truong
FROM hoc_sinh hs
JOIN truong_thcs thcs ON thcs.ma_truong = hs.ma_truong
JOIN ket_qua_phan_diem_thi k ON k.ma_truong = hs.ma_truong
    AND k.ma_diem_thi = hs.ma_diem_thi AND k.trang_thai_phan_diem = 'DaPhan'
ORDER BY hs.ma_diem_thi, hs.ma_hoc_sinh
SQL;
        return $this->db->query($sql . ($lock ? ' FOR UPDATE' : ''))->fetchAll();
    }

    // Truy vấn kết quả đã lưu.
    public function getAssignmentResults(bool $lock = false): array
    {
        $sql = 'SELECT ma_hoc_sinh, ma_diem_thi, ma_phong_thi, so_bao_danh
                FROM ket_qua_phan_phong_thi ORDER BY ma_hoc_sinh';
        return $this->db->query($sql . ($lock ? ' FOR UPDATE' : ''))->fetchAll();
    }

    public function source(bool $lock = false): array
    {
        $points = $this->getExamSites($lock);
        $rooms = $this->getExamRooms($lock);
        $students = $this->getEligibleStudents($lock);
        $results = $this->getAssignmentResults($lock);
        return compact('points', 'rooms', 'students', 'results');
    }

    public function confirm(array $draft): void
    {
        $this->db->beginTransaction();
        try {
            $source = $this->source(true);
            UC14Service::validate($draft, $source);
            $insert = $this->db->prepare(<<<'SQL'
INSERT INTO ket_qua_phan_phong_thi
(ma_hoc_sinh, ma_diem_thi, ma_phong_thi, so_bao_danh, trang_thai_phan_phong)
VALUES (:student, :point, :room, :sbd, 'DaPhan')
SQL);
            $update = $this->db->prepare("UPDATE hoc_sinh SET so_bao_danh = :sbd, trang_thai_phan_phong = 'DaPhan' WHERE ma_hoc_sinh = :student");
            foreach ($draft['assignments'] as $a) {
                $insert->execute([
                    ':student' => $a['ma_hoc_sinh'],
                    ':point' => $a['ma_diem_thi'],
                    ':room' => $a['ma_phong_thi'],
                    ':sbd' => $a['so_bao_danh'],
                ]);
                $update->execute([':sbd' => $a['so_bao_danh'], ':student' => $a['ma_hoc_sinh']]);
            }
            $this->db->exec(<<<'SQL'
UPDATE phong_thi p SET so_luong_thi_sinh_hien_tai = (
 SELECT COUNT(*) FROM ket_qua_phan_phong_thi k WHERE k.ma_phong_thi = p.ma_phong_thi
)
SQL);
            $this->db->exec(<<<'SQL'
UPDATE diem_thi d SET
 suc_chua_toi_da = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi),
 tong_so_thi_sinh = (SELECT COUNT(*) FROM hoc_sinh h WHERE h.ma_diem_thi = d.ma_diem_thi),
 suc_chua_con_lai = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi) -
                    (SELECT COUNT(*) FROM ket_qua_phan_phong_thi k WHERE k.ma_diem_thi = d.ma_diem_thi)
SQL);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $error;
        }
    }

    public function resetDemo(): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->exec('DELETE FROM ket_qua_phan_phong_thi');
            $this->db->exec("UPDATE hoc_sinh SET so_bao_danh = NULL, trang_thai_phan_phong = 'ChuaPhan'");
            $this->db->exec('UPDATE phong_thi SET so_luong_thi_sinh_hien_tai = 0');
            $this->db->exec(<<<'SQL'
UPDATE diem_thi d SET
 suc_chua_toi_da = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi),
 tong_so_thi_sinh = (SELECT COUNT(*) FROM hoc_sinh h WHERE h.ma_diem_thi = d.ma_diem_thi),
 suc_chua_con_lai = (SELECT COALESCE(SUM(p.suc_chua), 0) FROM phong_thi p WHERE p.ma_diem_thi = d.ma_diem_thi)
SQL);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $error;
        }
    }
}
