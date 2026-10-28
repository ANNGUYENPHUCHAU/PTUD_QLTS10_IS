-- Migration bổ sung các bảng cho UC09, UC21, UC22, UC23 (Thái Bá Cường)
USE uc14_demo;

-- 1. BẢNG NGƯỜI DÙNG / TÀI KHOẢN (UC21, UC23)
CREATE TABLE IF NOT EXISTS nguoi_dung (
    ma_nguoi_dung INT AUTO_INCREMENT PRIMARY KEY,
    ten_dang_nhap VARCHAR(50) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    ho_ten VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    so_dien_thoai VARCHAR(20) NOT NULL,
    vai_tro ENUM('QuanTriVien', 'CanBoSo', 'BanTuyenSinhTHPT', 'GiaoVuTHCS', 'ThuKy', 'HocSinh') NOT NULL DEFAULT 'CanBoSo',
    trang_thai ENUM('HoatDong', 'DaKhoa') NOT NULL DEFAULT 'HoatDong',
    quyen_han TEXT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. BẢNG NHẬT KÝ HỆ THỐNG / GIÁM SÁT (UC21, UC22, UC23)
CREATE TABLE IF NOT EXISTS nhat_ky_he_thong (
    ma_nhat_ky INT AUTO_INCREMENT PRIMARY KEY,
    loai_nhat_ky ENUM('DangNhap', 'QuanTri', 'HeThong', 'SaoLuu', 'Loi', 'BaoMat') NOT NULL DEFAULT 'HeThong',
    nguoi_thuc_hien VARCHAR(50) NOT NULL DEFAULT 'Hệ thống',
    hanh_dong VARCHAR(255) NOT NULL,
    chi_tiet TEXT NULL,
    dia_chi_ip VARCHAR(45) DEFAULT '127.0.0.1',
    thoi_gian TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. BẢNG CẤU HÌNH HỆ THỐNG (UC22)
CREATE TABLE IF NOT EXISTS cau_hinh_he_thong (
    khoa_cau_hinh VARCHAR(60) PRIMARY KEY,
    ten_cau_hinh VARCHAR(150) NOT NULL,
    gia_tri TEXT NOT NULL,
    kieu_du_lieu ENUM('text', 'number', 'date', 'boolean') DEFAULT 'text',
    nhom_cau_hinh VARCHAR(50) DEFAULT 'Tuyển sinh',
    mo_ta VARCHAR(255) NULL,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. BẢNG LỊCH SỬ SAO LƯU (UC22)
CREATE TABLE IF NOT EXISTS lich_su_sao_luu (
    ma_sao_luu INT AUTO_INCREMENT PRIMARY KEY,
    ten_file VARCHAR(255) NOT NULL,
    duong_dan VARCHAR(255) NOT NULL,
    dung_luong VARCHAR(50) NOT NULL,
    trang_thai ENUM('ThanhCong', 'ThatBai') NOT NULL DEFAULT 'ThanhCong',
    nguoi_thuc_hien VARCHAR(50) NOT NULL,
    ghi_chu VARCHAR(255) NULL,
    thoi_gian_sao_luu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. BẢNG TRƯỜNG THPT & CHỈ TIÊU (UC09)
CREATE TABLE IF NOT EXISTS truong_thpt (
    ma_truong_thpt VARCHAR(12) PRIMARY KEY,
    ten_truong VARCHAR(160) NOT NULL,
    dia_chi VARCHAR(255) NOT NULL,
    so_dien_thoai VARCHAR(20) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chi_tieu_thpt (
    ma_chi_tieu INT AUTO_INCREMENT PRIMARY KEY,
    ma_truong_thpt VARCHAR(12) NOT NULL,
    nam_tuyen_sinh INT NOT NULL,
    tong_chi_tieu INT NOT NULL DEFAULT 0,
    chi_tieu_con_lai INT NOT NULL DEFAULT 0,
    diem_chuan_nv1 DECIMAL(4,2) DEFAULT 0,
    diem_chuan_nv2 DECIMAL(4,2) DEFAULT 0,
    diem_chuan_nv3 DECIMAL(4,2) DEFAULT 0,
    UNIQUE KEY uniq_truong_nam (ma_truong_thpt, nam_tuyen_sinh),
    CONSTRAINT fk_ct_truong FOREIGN KEY (ma_truong_thpt) REFERENCES truong_thpt(ma_truong_thpt)
) ENGINE=InnoDB;

-- 6. BẢNG THỐNG KÊ CHI TIẾT HỒ SƠ / KẾT QUẢ TUYỂN SINH CỦA THÍ SINH THEO TRƯỜNG (UC09)
CREATE TABLE IF NOT EXISTS ho_so_tuyen_sinh_thpt (
    ma_ho_so INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoc_sinh VARCHAR(12) NOT NULL,
    nam_tuyen_sinh INT NOT NULL DEFAULT 2026,
    ma_truong_thpt VARCHAR(12) NOT NULL,
    nguyen_vong INT NOT NULL COMMENT '1: NV1, 2: NV2, 3: NV3',
    diem_xet_tuyen DECIMAL(4,2) NOT NULL DEFAULT 0,
    trang_thai_ho_so ENUM('HopLe', 'KhongHopLe') DEFAULT 'HopLe',
    ket_qua_tuyen_sinh ENUM('TrungTuyen', 'KhongTrungTuyen', 'DangXet') DEFAULT 'DangXet',
    xac_nhan_nhap_hoc ENUM('DaXacNhan', 'ChuaXacNhan') DEFAULT 'ChuaXacNhan',
    thoi_gian_xac_nhan TIMESTAMP NULL,
    CONSTRAINT fk_hs_thpt FOREIGN KEY (ma_truong_thpt) REFERENCES truong_thpt(ma_truong_thpt)
) ENGINE=InnoDB;

-- DỮ LIỆU KHỞI TẠO MẪU

-- Dữ liệu tài khoản
INSERT IGNORE INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, email, so_dien_thoai, vai_tro, trang_thai, quyen_han) VALUES
('admin', '123456', 'Quản trị viên Hệ thống', 'admin@tuyensinh10.edu.vn', '0901234567', 'QuanTriVien', 'HoatDong', 'ALL'),
('canbo', 'Demo@123', 'Nguyễn Văn Cán Bộ', 'canbo@sogddt.edu.vn', '0912345678', 'CanBoSo', 'HoatDong', 'UC14,UC09'),
('bantuyensinh_nh', '123456', 'Ban Tuyển Sinh THPT Nguyễn Huệ', 'tuyensinh.nguyenhue@hcm.edu.vn', '0923456789', 'BanTuyenSinhTHPT', 'HoatDong', 'UC09'),
('bantuyensinh_tp', '123456', 'Ban Tuyển Sinh THPT Trần Phú', 'tuyensinh.tranphu@hcm.edu.vn', '0934567890', 'BanTuyenSinhTHPT', 'HoatDong', 'UC09'),
('giaovu_nd', '123456', 'Giáo vụ THCS Nguyễn Du', 'giaovu.nguyendu@hcm.edu.vn', '0945678901', 'GiaoVuTHCS', 'HoatDong', 'UC07'),
('thuky01', '123456', 'Trần Thị Thư Ký', 'thuky@sogddt.edu.vn', '0956789012', 'ThuKy', 'HoatDong', 'UC19,UC20'),
('hocsinh01', '123456', 'Nguyễn Văn An', 'hs.an@gmail.com', '0967890123', 'HocSinh', 'HoatDong', 'UC01,UC04,UC05,UC06');

-- Dữ liệu trường THPT
INSERT IGNORE INTO truong_thpt (ma_truong_thpt, ten_truong, dia_chi, so_dien_thoai) VALUES
('THPT01', 'THPT Nguyễn Huệ', 'Số 123 Quang Trung, Quận Gò Vấp, TP.HCM', '02838941234'),
('THPT02', 'THPT Trần Phú', 'Số 45 Lê Thúc Hoạch, Quận Tân Phú, TP.HCM', '02838604321'),
('THPT03', 'THPT Gia Định', 'Số 195/29 Xô Viết Nghệ Tĩnh, Quận Bình Thạnh, TP.HCM', '02838991234');

-- Dữ liệu chỉ tiêu tuyển sinh theo năm
INSERT IGNORE INTO chi_tieu_thpt (ma_truong_thpt, nam_tuyen_sinh, tong_chi_tieu, chi_tieu_con_lai, diem_chuan_nv1, diem_chuan_nv2, diem_chuan_nv3) VALUES
('THPT01', 2026, 450, 18, 22.50, 23.50, 24.50),
('THPT02', 2026, 380, 24, 20.75, 21.75, 22.75),
('THPT03', 2026, 520, 0,  24.00, 25.00, 26.00),
('THPT01', 2025, 420, 0,  21.75, 22.75, 23.75),
('THPT02', 2025, 360, 0,  20.00, 21.00, 22.00);

-- Dữ liệu cấu hình hệ thống
INSERT IGNORE INTO cau_hinh_he_thong (khoa_cau_hinh, ten_cau_hinh, gia_tri, kieu_du_lieu, nhom_cau_hinh, mo_ta) VALUES
('nam_tuyen_sinh_hien_tai', 'Năm tuyển sinh hiện tại', '2026', 'number', 'Tuyển sinh', 'Năm học tổ chức kỳ thi tuyển sinh vào lớp 10'),
('so_nguyen_vong_toi_da', 'Số nguyện vọng tối đa', '3', 'number', 'Tuyển sinh', 'Số lượng nguyện vọng công lập tối đa thí sinh được đăng ký'),
('thoi_gian_mo_dang_ky', 'Thời gian mở đăng ký NV', '2026-05-01 08:00:00', 'date', 'Lịch trình', 'Thời điểm thí sinh bắt đầu được đăng ký nguyện vọng'),
('thoi_gian_dong_dang_ky', 'Thời gian khóa đăng ký NV', '2026-05-20 17:00:00', 'date', 'Lịch trình', 'Hạn chót thí sinh được phép nộp và điều chỉnh nguyện vọng'),
('cho_phep_dieu_chinh_nv', 'Cho phép điều chỉnh nguyện vọng', '1', 'boolean', 'Quy tắc', '1: Đang cho phép điều chỉnh; 0: Đã khóa điều chỉnh'),
('thoi_gian_xac_nhan_nhap_hoc', 'Hạn chót xác nhận nhập học', '2026-07-15 17:00:00', 'date', 'Lịch trình', 'Hạn cuối cùng để thí sinh trúng tuyển xác nhận trực tuyến');

-- Dữ liệu lịch sử nhật ký mẫu
INSERT IGNORE INTO nhat_ky_he_thong (loai_nhat_ky, nguoi_thuc_hien, hanh_dong, chi_tiet) VALUES
('DangNhap', 'admin', 'Đăng nhập hệ thống thành công', 'IP: 127.0.0.1, Trình duyệt: Chrome/Windows'),
('QuanTri', 'admin', 'Cập nhật tài khoản người dùng', 'Cập nhật quyền hạn cho tài khoản canbo'),
('HeThong', 'admin', 'Lưu cấu hình hệ thống', 'Cập nhật tham số: nam_tuyen_sinh_hien_tai = 2026'),
('SaoLuu', 'admin', 'Tạo bản sao lưu dữ liệu tự động', 'File: backup_20261028_230000.sql, Dung lượng: 450 KB');

-- Dữ liệu lịch sử sao lưu mẫu
INSERT IGNORE INTO lich_su_sao_luu (ten_file, duong_dan, dung_luong, trang_thai, nguoi_thuc_hien, ghi_chu) VALUES
('backup_uc14_demo_20261020.sql', 'backups/backup_uc14_demo_20261020.sql', '420 KB', 'ThanhCong', 'admin', 'Sao lưu định kỳ tuần 8'),
('backup_uc14_demo_20261027.sql', 'backups/backup_uc14_demo_20261027.sql', '458 KB', 'ThanhCong', 'admin', 'Sao lưu trước khi import dữ liệu học sinh');

-- Dữ liệu mẫu hồ sơ tuyển sinh phục vụ thống kê UC09
INSERT IGNORE INTO ho_so_tuyen_sinh_thpt (ma_hoc_sinh, nam_tuyen_sinh, ma_truong_thpt, nguyen_vong, diem_xet_tuyen, trang_thai_ho_so, ket_qua_tuyen_sinh, xac_nhan_nhap_hoc, thoi_gian_xac_nhan) VALUES
('HS001', 2026, 'THPT01', 1, 24.50, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 09:15:00'),
('HS002', 2026, 'THPT01', 1, 23.75, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 10:20:00'),
('HS003', 2026, 'THPT01', 1, 23.00, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 11:00:00'),
('HS004', 2026, 'THPT01', 1, 22.75, 'HopLe', 'TrungTuyen', 'ChuaXacNhan', NULL),
('HS005', 2026, 'THPT01', 1, 22.50, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-03 14:10:00'),
('HS006', 2026, 'THPT01', 1, 21.50, 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('HS007', 2026, 'THPT01', 1, 20.00, 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('HS008', 2026, 'THPT01', 2, 24.00, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-04 08:30:00'),
('HS009', 2026, 'THPT01', 2, 23.50, 'HopLe', 'TrungTuyen', 'ChuaXacNhan', NULL),
('HS010', 2026, 'THPT01', 2, 22.00, 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('HS011', 2026, 'THPT01', 3, 25.00, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-05 16:45:00'),
('HS012', 2026, 'THPT01', 3, 23.00, 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('HS013', 2026, 'THPT02', 1, 22.00, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 08:45:00'),
('HS014', 2026, 'THPT02', 1, 21.50, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 09:30:00'),
('HS015', 2026, 'THPT02', 1, 20.75, 'HopLe', 'TrungTuyen', 'ChuaXacNhan', NULL),
('HS016', 2026, 'THPT02', 1, 19.50, 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('HS017', 2026, 'THPT02', 2, 22.50, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-03 10:15:00'),
('HS018', 2026, 'THPT02', 2, 21.00, 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('HS019', 2026, 'THPT02', 3, 23.00, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-04 15:00:00'),
('HS020', 2026, 'THPT03', 1, 26.50, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 14:00:00'),
('HS021', 2026, 'THPT03', 1, 25.00, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 15:30:00'),
('HS022', 2026, 'THPT03', 1, 24.00, 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-03 09:00:00'),
('HS023', 2026, 'THPT03', 1, 23.50, 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL);
