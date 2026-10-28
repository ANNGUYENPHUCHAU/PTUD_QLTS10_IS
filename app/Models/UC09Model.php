<?php
declare(strict_types=1);

/**
 * Model cho Use Case 09: Thống kê kết quả tuyển sinh
 * Phục vụ Ban tuyển sinh trường THPT xem và tổng hợp báo cáo.
 */
final class UC09Model
{
    public function __construct(private PDO $db) {}

    /**
     * Lấy danh sách các trường THPT có trong hệ thống
     */
    public function getHighSchools(): array
    {
        $stmt = $this->db->query('SELECT ma_truong_thpt, ten_truong, dia_chi FROM truong_thpt ORDER BY ma_truong_thpt');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách các năm tuyển sinh có dữ liệu
     */
    public function getAdmissionYears(): array
    {
        $stmt = $this->db->query('SELECT DISTINCT nam_tuyen_sinh FROM chi_tieu_thpt ORDER BY nam_tuyen_sinh DESC');
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return !empty($years) ? $years : [2026, 2025];
    }

    /**
     * Lấy thông tin chỉ tiêu và điểm chuẩn của trường theo năm
     */
    public function getTargetInfo(string $schoolId, int $year): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ct.*, t.ten_truong, t.dia_chi 
             FROM chi_tieu_thpt ct
             JOIN truong_thpt t ON t.ma_truong_thpt = ct.ma_truong_thpt
             WHERE ct.ma_truong_thpt = :schoolId AND ct.nam_tuyen_sinh = :year'
        );
        $stmt->execute(['schoolId' => $schoolId, 'year' => $year]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Tổng hợp số liệu thống kê tổng quan theo trường và năm
     */
    public function getStatistics(string $schoolId, int $year): array
    {
        $target = $this->getTargetInfo($schoolId, $year);
        $tongChiTieu = $target ? (int)$target['tong_chi_tieu'] : 0;

        // Tổng số hồ sơ đăng ký
        $stmt = $this->db->prepare(
            'SELECT 
                COUNT(*) as tong_ho_so,
                SUM(CASE WHEN nguyen_vong = 1 THEN 1 ELSE 0 END) as so_nv1,
                SUM(CASE WHEN nguyen_vong = 2 THEN 1 ELSE 0 END) as so_nv2,
                SUM(CASE WHEN nguyen_vong = 3 THEN 1 ELSE 0 END) as so_nv3,
                SUM(CASE WHEN trang_thai_ho_so = "HopLe" THEN 1 ELSE 0 END) as du_dieu_kien,
                SUM(CASE WHEN ket_qua_tuyen_sinh = "TrungTuyen" THEN 1 ELSE 0 END) as so_trung_tuyen,
                SUM(CASE WHEN ket_qua_tuyen_sinh = "TrungTuyen" AND nguyen_vong = 1 THEN 1 ELSE 0 END) as trung_tuyen_nv1,
                SUM(CASE WHEN ket_qua_tuyen_sinh = "TrungTuyen" AND nguyen_vong = 2 THEN 1 ELSE 0 END) as trung_tuyen_nv2,
                SUM(CASE WHEN ket_qua_tuyen_sinh = "TrungTuyen" AND nguyen_vong = 3 THEN 1 ELSE 0 END) as trung_tuyen_nv3,
                SUM(CASE WHEN ket_qua_tuyen_sinh = "KhongTrungTuyen" THEN 1 ELSE 0 END) as so_truot,
                SUM(CASE WHEN xac_nhan_nhap_hoc = "DaXacNhan" THEN 1 ELSE 0 END) as da_nhap_hoc
             FROM ho_so_tuyen_sinh_thpt
             WHERE ma_truong_thpt = :schoolId AND nam_tuyen_sinh = :year'
        );
        $stmt->execute(['schoolId' => $schoolId, 'year' => $year]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $tongHoSo = (int)($stats['tong_ho_so'] ?? 0);
        $soTrungTuyen = (int)($stats['so_trung_tuyen'] ?? 0);
        $daNhapHoc = (int)($stats['da_nhap_hoc'] ?? 0);
        $tyLeChoi = $tongChiTieu > 0 ? round($tongHoSo / $tongChiTieu, 2) : 0.0;
        $tyLeNhapHoc = $soTrungTuyen > 0 ? round(($daNhapHoc / $soTrungTuyen) * 100, 1) : 0.0;
        $chiTieuConLai = max(0, $tongChiTieu - $soTrungTuyen);

        return [
            'target' => $target,
            'tong_chi_tieu' => $tongChiTieu,
            'chi_tieu_con_lai' => $chiTieuConLai,
            'tong_ho_so' => $tongHoSo,
            'so_nv1' => (int)($stats['so_nv1'] ?? 0),
            'so_nv2' => (int)($stats['so_nv2'] ?? 0),
            'so_nv3' => (int)($stats['so_nv3'] ?? 0),
            'du_dieu_kien' => (int)($stats['du_dieu_kien'] ?? 0),
            'so_trung_tuyen' => $soTrungTuyen,
            'trung_tuyen_nv1' => (int)($stats['trung_tuyen_nv1'] ?? 0),
            'trung_tuyen_nv2' => (int)($stats['trung_tuyen_nv2'] ?? 0),
            'trung_tuyen_nv3' => (int)($stats['trung_tuyen_nv3'] ?? 0),
            'so_truot' => (int)($stats['so_truot'] ?? 0),
            'da_nhap_hoc' => $daNhapHoc,
            'ty_le_choi' => $tyLeChoi,
            'ty_le_nhap_hoc' => $tyLeNhapHoc,
        ];
    }

    /**
     * Lấy danh sách hồ sơ chi tiết theo bộ lọc tiêu chí
     */
    public function getFilteredCandidates(string $schoolId, int $year, string $criterion = 'all'): array
    {
        $sql = 'SELECT hs.*, h.ho_ten, t.ten_truong as ten_thcs
                FROM ho_so_tuyen_sinh_thpt hs
                LEFT JOIN hoc_sinh h ON h.ma_hoc_sinh = hs.ma_hoc_sinh
                LEFT JOIN truong_thcs t ON t.ma_truong = h.ma_truong
                WHERE hs.ma_truong_thpt = :schoolId AND hs.nam_tuyen_sinh = :year';

        if ($criterion === 'trung_tuyen') {
            $sql .= ' AND hs.ket_qua_tuyen_sinh = "TrungTuyen"';
        } elseif ($criterion === 'truot') {
            $sql .= ' AND hs.ket_qua_tuyen_sinh = "KhongTrungTuyen"';
        } elseif ($criterion === 'da_nhap_hoc') {
            $sql .= ' AND hs.xac_nhan_nhap_hoc = "DaXacNhan"';
        } elseif ($criterion === 'du_dieu_kien') {
            $sql .= ' AND hs.trang_thai_ho_so = "HopLe"';
        }

        $sql .= ' ORDER BY hs.diem_xet_tuyen DESC, hs.nguyen_vong ASC, hs.ma_hoc_sinh ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['schoolId' => $schoolId, 'year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
