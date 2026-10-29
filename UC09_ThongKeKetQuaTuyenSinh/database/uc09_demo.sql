-- =======================================================
-- CƠ SỞ DỮ LIỆU DEMO CHO UC09: THỐNG KÊ KẾT QUẢ TUYỂN SINH
-- Hệ thống Quản lý Tuyển sinh Lớp 10
-- =======================================================

CREATE DATABASE IF NOT EXISTS uc14_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uc14_demo;

-- 1. BẢNG TRƯỜNG THCS (dùng để hiển thị trường cấp 2 của thí sinh)
CREATE TABLE IF NOT EXISTS truong_thcs (
    ma_truong VARCHAR(20) PRIMARY KEY,
    ten_truong VARCHAR(150) NOT NULL,
    quan_huyen VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO truong_thcs (ma_truong, ten_truong, quan_huyen) VALUES
('THCS_01', 'THCS Lê Quý Đôn', 'Quận 3'),
('THCS_02', 'THCS Nguyễn Du', 'Quận 1'),
('THCS_03', 'THCS Colette', 'Quận 3'),
('THCS_04', 'THCS Ba Đình', 'Quận 5');

-- 2. BẢNG HỌC SINH / THÍ SINH
CREATE TABLE IF NOT EXISTS hoc_sinh (
    ma_hoc_sinh VARCHAR(20) PRIMARY KEY,
    ho_ten VARCHAR(150) NOT NULL,
    ngay_sinh DATE NOT NULL,
    gioi_tinh VARCHAR(10) NOT NULL,
    ma_truong VARCHAR(20) NULL,
    CONSTRAINT fk_hs_thcs FOREIGN KEY (ma_truong) REFERENCES truong_thcs(ma_truong)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO hoc_sinh (ma_hoc_sinh, ho_ten, ngay_sinh, gioi_tinh, ma_truong) VALUES
('HS001', 'Nguyễn Văn An', '2011-03-12', 'Nam', 'THCS_01'),
('HS002', 'Trần Thị Bích', '2011-05-20', 'Nữ', 'THCS_01'),
('HS003', 'Lê Hoàng Cường', '2011-08-15', 'Nam', 'THCS_02'),
('HS004', 'Phạm Minh Đức', '2011-01-10', 'Nam', 'THCS_02'),
('HS005', 'Võ Thùy Dung', '2011-11-25', 'Nữ', 'THCS_03'),
('HS006', 'Đặng Quốc Hùng', '2011-04-18', 'Nam', 'THCS_03'),
('HS007', 'Bùi Ngọc Lan', '2011-09-02', 'Nữ', 'THCS_04'),
('HS008', 'Hoàng Gia Huy', '2011-07-14', 'Nam', 'THCS_01'),
('HS009', 'Ngô Thu Thảo', '2011-12-01', 'Nữ', 'THCS_02'),
('HS010', 'Đỗ Thành Long', '2011-06-30', 'Nam', 'THCS_04'),
('HS011', 'Trịnh Khánh Vy', '2011-02-14', 'Nữ', 'THCS_01'),
('HS012', 'Phan Hữu Nghĩa', '2011-10-09', 'Nam', 'THCS_03'),
('HS013', 'Lý Bảo Trâm', '2011-07-22', 'Nữ', 'THCS_02'),
('HS014', 'Dương Tuấn Khải', '2011-03-05', 'Nam', 'THCS_04'),
('HS015', 'Mai Thanh Vân', '2011-08-19', 'Nữ', 'THCS_01'),
('HS016', 'Tạ Quốc Bảo', '2011-12-11', 'Nam', 'THCS_03'),
('HS017', 'Vũ Quỳnh Như', '2011-05-04', 'Nữ', 'THCS_02'),
('HS018', 'Đinh Công Phượng', '2011-09-17', 'Nam', 'THCS_04'),
('HS019', 'Lưu Ánh Nguyệt', '2011-06-08', 'Nữ', 'THCS_01'),
('HS020', 'Châu Vĩnh Phúc', '2011-01-29', 'Nam', 'THCS_03'),
('HS021', 'Quách Mỹ Linh', '2011-04-03', 'Nữ', 'THCS_02'),
('HS022', 'Hồ Quang Hiếu', '2011-11-13', 'Nam', 'THCS_04'),
('HS023', 'Thái Kim Yến', '2011-10-21', 'Nữ', 'THCS_01');

-- 3. BẢNG TRƯỜNG THPT
CREATE TABLE IF NOT EXISTS truong_thpt (
    ma_truong_thpt VARCHAR(12) PRIMARY KEY,
    ten_truong VARCHAR(160) NOT NULL,
    dia_chi VARCHAR(255) NOT NULL,
    so_dien_thoai VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO truong_thpt (ma_truong_thpt, ten_truong, dia_chi, so_dien_thoai) VALUES
('THPT01', 'THPT Nguyễn Huệ', 'Số 123 Quang Trung, Quận Gò Vấp, TP.HCM', '02838941234'),
('THPT02', 'THPT Trần Phú', 'Số 45 Lê Thúc Hoạch, Quận Tân Phú, TP.HCM', '02838604321'),
('THPT03', 'THPT Gia Định', 'Số 195/29 Xô Viết Nghệ Tĩnh, Quận Bình Thạnh, TP.HCM', '02838991234');

-- 4. BẢNG CHỈ TIÊU & ĐIỂM CHUẨN TRƯỜNG THPT THEO NĂM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO chi_tieu_thpt (ma_truong_thpt, nam_tuyen_sinh, tong_chi_tieu, chi_tieu_con_lai, diem_chuan_nv1, diem_chuan_nv2, diem_chuan_nv3) VALUES
('THPT01', 2026, 450, 18, 22.50, 23.50, 24.50),
('THPT02', 2026, 380, 24, 20.75, 21.75, 22.75),
('THPT03', 2026, 520, 0,  24.00, 25.00, 26.00),
('THPT01', 2025, 420, 0,  21.75, 22.75, 23.75),
('THPT02', 2025, 360, 0,  20.00, 21.00, 22.00);

-- 5. BẢNG HỒ SƠ ĐĂNG KÝ VÀ KẾT QUẢ TUYỂN SINH
CREATE TABLE IF NOT EXISTS ho_so_tuyen_sinh_thpt (
    ma_ho_so INT AUTO_INCREMENT PRIMARY KEY,
    ma_hoc_sinh VARCHAR(20) NOT NULL,
    nam_tuyen_sinh INT NOT NULL DEFAULT 2026,
    ma_truong_thpt VARCHAR(12) NOT NULL,
    nguyen_vong INT NOT NULL COMMENT '1: NV1, 2: NV2, 3: NV3',
    diem_xet_tuyen DECIMAL(4,2) NOT NULL DEFAULT 0,
    trang_thai_ho_so ENUM('HopLe', 'KhongHopLe') DEFAULT 'HopLe',
    ket_qua_tuyen_sinh ENUM('TrungTuyen', 'KhongTrungTuyen', 'DangXet') DEFAULT 'DangXet',
    xac_nhan_nhap_hoc ENUM('DaXacNhan', 'ChuaXacNhan') DEFAULT 'ChuaXacNhan',
    thoi_gian_xac_nhan TIMESTAMP NULL,
    CONSTRAINT fk_hs_thpt FOREIGN KEY (ma_truong_thpt) REFERENCES truong_thpt(ma_truong_thpt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
