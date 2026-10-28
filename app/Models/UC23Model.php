<?php
declare(strict_types=1);

/**
 * Model cho Use Case 23: Quên mật khẩu
 * Phục vụ người dùng hệ thống xác thực và đặt lại mật khẩu.
 */
final class UC23Model
{
    public function __construct(private PDO $db) {}

    /**
     * Xác minh tài khoản dựa trên tên đăng nhập và email (hoặc số điện thoại)
     */
    public function verifyAccount(string $username, string $emailOrPhone): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ma_nguoi_dung, ten_dang_nhap, ho_ten, email, so_dien_thoai, trang_thai 
             FROM nguoi_dung 
             WHERE ten_dang_nhap = :u AND (email = :em OR so_dien_thoai = :ph)'
        );
        $stmt->execute(['u' => $username, 'em' => $emailOrPhone, 'ph' => $emailOrPhone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Đặt lại mật khẩu mới cho tài khoản
     */
    public function resetPassword(int $userId, string $newPassword, string $username): void
    {
        $stmt = $this->db->prepare('UPDATE nguoi_dung SET mat_khau = :p WHERE ma_nguoi_dung = :id');
        $stmt->execute(['p' => $newPassword, 'id' => $userId]);

        // Ghi lại nhật ký an ninh
        try {
            $stmtLog = $this->db->prepare(
                'INSERT INTO nhat_ky_he_thong (loai_nhat_ky, nguoi_thuc_hien, hanh_dong, chi_tiet)
                 VALUES ("BaoMat", :u, "Đặt lại mật khẩu", "Người dùng tự đặt lại mật khẩu qua chức năng Quên mật khẩu")'
            );
            $stmtLog->execute(['u' => $username]);
        } catch (Throwable) {}
    }
}
