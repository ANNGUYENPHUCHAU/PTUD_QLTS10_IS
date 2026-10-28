<?php
declare(strict_types=1);

/**
 * Model cho Use Case 21: Quản lý tài khoản và phân quyền
 * Phục vụ Quản trị viên hệ thống.
 */
final class UC21Model
{
    public function __construct(private PDO $db) {}

    /**
     * Danh sách tài khoản có tìm kiếm và lọc theo vai trò, trạng thái
     */
    public function getUsers(string $keyword = '', string $role = '', string $status = ''): array
    {
        $sql = 'SELECT ma_nguoi_dung, ten_dang_nhap, ho_ten, email, so_dien_thoai, vai_tro, trang_thai, quyen_han, ngay_tao 
                FROM nguoi_dung WHERE 1=1';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' AND (ten_dang_nhap LIKE :kw1 OR ho_ten LIKE :kw2 OR email LIKE :kw3)';
            $params['kw1'] = "%$keyword%";
            $params['kw2'] = "%$keyword%";
            $params['kw3'] = "%$keyword%";
        }

        if ($role !== '' && $role !== 'all') {
            $sql .= ' AND vai_tro = :role';
            $params['role'] = $role;
        }

        if ($status !== '' && $status !== 'all') {
            $sql .= ' AND trang_thai = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY ma_nguoi_dung DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết một tài khoản theo ID
     */
    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM nguoi_dung WHERE ma_nguoi_dung = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Kiểm tra tên đăng nhập đã tồn tại chưa (trừ ID hiện tại nếu là cập nhật)
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM nguoi_dung WHERE ten_dang_nhap = :username';
        $params = ['username' => $username];
        if ($excludeId !== null) {
            $sql .= ' AND ma_nguoi_dung != :id';
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Thêm mới tài khoản
     */
    public function createUser(array $data, string $adminUser = 'admin'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, email, so_dien_thoai, vai_tro, trang_thai, quyen_han)
             VALUES (:u, :p, :name, :email, :phone, :role, :status, :perms)'
        );
        $stmt->execute([
            'u' => $data['ten_dang_nhap'],
            'p' => $data['mat_khau'],
            'name' => $data['ho_ten'],
            'email' => $data['email'],
            'phone' => $data['so_dien_thoai'],
            'role' => $data['vai_tro'],
            'status' => $data['trang_thai'] ?? 'HoatDong',
            'perms' => $data['quyen_han'] ?? '',
        ]);
        $newId = (int)$this->db->lastInsertId();

        $this->logAction($adminUser, 'Tạo tài khoản mới', "Tạo người dùng: {$data['ten_dang_nhap']} (Vai trò: {$data['vai_tro']})");
        return $newId;
    }

    /**
     * Cập nhật thông tin tài khoản
     */
    public function updateUser(int $id, array $data, string $adminUser = 'admin'): void
    {
        $sql = 'UPDATE nguoi_dung SET ho_ten = :name, email = :email, so_dien_thoai = :phone, vai_tro = :role, quyen_han = :perms';
        $params = [
            'id' => $id,
            'name' => $data['ho_ten'],
            'email' => $data['email'],
            'phone' => $data['so_dien_thoai'],
            'role' => $data['vai_tro'],
            'perms' => $data['quyen_han'] ?? '',
        ];

        if (!empty($data['mat_khau'])) {
            $sql .= ', mat_khau = :pass';
            $params['pass'] = $data['mat_khau'];
        }

        $sql .= ' WHERE ma_nguoi_dung = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $this->logAction($adminUser, 'Cập nhật tài khoản', "Cập nhật thông tin tài khoản ID #$id");
    }

    /**
     * Khóa hoặc mở khóa tài khoản
     */
    public function toggleStatus(int $id, string $adminUser = 'admin'): string
    {
        $user = $this->getUserById($id);
        if (!$user) {
            throw new InvalidArgumentException('Tài khoản không tồn tại.');
        }

        $newStatus = ($user['trang_thai'] === 'HoatDong') ? 'DaKhoa' : 'HoatDong';
        $stmt = $this->db->prepare('UPDATE nguoi_dung SET trang_thai = :st WHERE ma_nguoi_dung = :id');
        $stmt->execute(['st' => $newStatus, 'id' => $id]);

        $actionText = ($newStatus === 'DaKhoa') ? 'Khóa tài khoản' : 'Mở khóa tài khoản';
        $this->logAction($adminUser, $actionText, "$actionText: {$user['ten_dang_nhap']} (ID #$id)");
        return $newStatus;
    }

    /**
     * Ghi nhật ký quản trị
     */
    public function logAction(string $adminUser, string $action, string $details): void
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO nhat_ky_he_thong (loai_nhat_ky, nguoi_thuc_hien, hanh_dong, chi_tiet)
                 VALUES ("QuanTri", :admin, :action, :details)'
            );
            $stmt->execute([
                'admin' => $adminUser,
                'action' => $action,
                'details' => $details,
            ]);
        } catch (Throwable) {}
    }
}
