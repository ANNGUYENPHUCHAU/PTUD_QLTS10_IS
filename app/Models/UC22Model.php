<?php
declare(strict_types=1);

/**
 * Model cho Use Case 22: Cấu hình, sao lưu và giám sát hệ thống
 * Phục vụ Quản trị viên hệ thống.
 */
final class UC22Model
{
    public function __construct(private PDO $db) {}

    // ========================================================
    // PHÂN HỆ 1: CẤU HÌNH HỆ THỐNG
    // ========================================================

    public function getConfigurations(): array
    {
        $stmt = $this->db->query('SELECT * FROM cau_hinh_he_thong ORDER BY nhom_cau_hinh, khoa_cau_hinh');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateConfiguration(string $key, string $value, string $adminUser = 'admin'): void
    {
        $stmt = $this->db->prepare('UPDATE cau_hinh_he_thong SET gia_tri = :val WHERE khoa_cau_hinh = :k');
        $stmt->execute(['val' => $value, 'k' => $key]);

        $this->logAction('HeThong', $adminUser, 'Cập nhật cấu hình', "Thay đổi giá trị tham số [$key] thành '$value'");
    }

    // ========================================================
    // PHÂN HỆ 2: SAO LƯU DỮ LIỆU
    // ========================================================

    public function getBackupHistory(): array
    {
        $stmt = $this->db->query('SELECT * FROM lich_su_sao_luu ORDER BY ma_sao_luu DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo một bản sao lưu toàn bộ cơ sở dữ liệu mẫu uc14_demo thành file .sql
     */
    public function createDatabaseBackup(string $backupDir, string $adminUser = 'admin'): array
    {
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $filename = 'backup_uc14_demo_' . date('Ymd_His') . '.sql';
        $filepath = rtrim($backupDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        // Sinh nội dung SQL backup
        $sqlContent = "-- BẢN SAO LƯU DỮ LIỆU HỆ THỐNG QLTS LỚP 10\n";
        $sqlContent .= "-- Thời gian tạo: " . date('Y-m-d H:i:s') . "\n";
        $sqlContent .= "-- Người thực hiện: " . $adminUser . "\n\n";
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        // Lấy danh sách bảng
        $tables = $this->db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $tbl) {
            // Không sao lưu bảng nhật ký và lịch sử sao lưu để tránh phình dung lượng
            if ($tbl === 'lich_su_sao_luu') {
                continue;
            }

            // Cấu trúc bảng
            $createStmt = $this->db->query("SHOW CREATE TABLE `$tbl`")->fetch(PDO::FETCH_NUM);
            $sqlContent .= "DROP TABLE IF EXISTS `$tbl`;\n";
            $sqlContent .= ($createStmt[1] ?? '') . ";\n\n";

            // Dữ liệu bảng
            $rows = $this->db->query("SELECT * FROM `$tbl`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $colNames = implode('`, `', $columns);
                $sqlContent .= "INSERT INTO `$tbl` (`$colNames`) VALUES\n";

                $valRows = [];
                foreach ($rows as $row) {
                    $escaped = array_map(function ($val) {
                        return ($val === null) ? 'NULL' : $this->db->quote((string)$val);
                    }, array_values($row));
                    $valRows[] = "(" . implode(', ', $escaped) . ")";
                }
                $sqlContent .= implode(",\n", $valRows) . ";\n\n";
            }
        }
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($filepath, $sqlContent);
        $sizeBytes = filesize($filepath);
        $sizeStr = ($sizeBytes >= 1048576) ? round($sizeBytes / 1048576, 2) . ' MB' : round($sizeBytes / 1024, 1) . ' KB';

        // Ghi lại vào lịch sử sao lưu
        $stmt = $this->db->prepare(
            'INSERT INTO lich_su_sao_luu (ten_file, duong_dan, dung_luong, trang_thai, nguoi_thuc_hien, ghi_chu)
             VALUES (:f, :p, :s, "ThanhCong", :user, :note)'
        );
        $stmt->execute([
            'f' => $filename,
            'p' => 'database/backups/' . $filename,
            's' => $sizeStr,
            'user' => $adminUser,
            'note' => 'Bản sao lưu dữ liệu toàn diện định dạng SQL',
        ]);

        $this->logAction('SaoLuu', $adminUser, 'Tạo bản sao lưu dữ liệu', "Tạo file sao lưu: $filename ($sizeStr)");

        return [
            'filename' => $filename,
            'size' => $sizeStr,
            'path' => $filepath,
        ];
    }

    // ========================================================
    // PHÂN HỆ 3: GIÁM SÁT HỆ THỐNG (LOGS)
    // ========================================================

    public function getSystemLogs(string $type = 'all', string $keyword = '', int $limit = 50): array
    {
        $sql = 'SELECT * FROM nhat_ky_he_thong WHERE 1=1';
        $params = [];

        if ($type !== 'all' && $type !== '') {
            $sql .= ' AND loai_nhat_ky = :type';
            $params['type'] = $type;
        }

        if ($keyword !== '') {
            $sql .= ' AND (hanh_dong LIKE :kw1 OR chi_tiet LIKE :kw2 OR nguoi_thuc_hien LIKE :kw3)';
            $params['kw1'] = "%$keyword%";
            $params['kw2'] = "%$keyword%";
            $params['kw3'] = "%$keyword%";
        }

        $sql .= ' ORDER BY ma_nhat_ky DESC LIMIT ' . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logAction(string $type, string $user, string $action, string $details): void
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO nhat_ky_he_thong (loai_nhat_ky, nguoi_thuc_hien, hanh_dong, chi_tiet)
                 VALUES (:t, :u, :a, :d)'
            );
            $stmt->execute(['t' => $type, 'u' => $user, 'a' => $action, 'd' => $details]);
        } catch (Throwable) {}
    }
}
