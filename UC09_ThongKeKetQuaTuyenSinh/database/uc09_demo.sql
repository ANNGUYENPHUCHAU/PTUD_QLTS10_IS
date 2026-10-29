-- =======================================================
-- CƠ SỞ DỮ LIỆU DEMO CHO UC09: THỐNG KÊ KẾT QUẢ TUYỂN SINH
-- Tương thích 100% với cơ sở dữ liệu chung uc14_demo
-- =======================================================

CREATE DATABASE IF NOT EXISTS uc14_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uc14_demo;

-- 1. BẢNG TRƯỜNG THCS (Tương thích cấu trúc chung của hệ thống)
CREATE TABLE IF NOT EXISTS truong_thcs (
    ma_truong VARCHAR(12) PRIMARY KEY,
    ten_truong VARCHAR(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO truong_thcs (ma_truong, ten_truong) VALUES
('THCS01', 'THCS Nguyễn Du'),
('THCS02', 'THCS Lê Quý Đôn'),
('THCS03', 'THCS Nguyễn Trãi');

-- 2. BẢNG ĐIỂM THI (Dùng cho khóa ngoại của học sinh nếu chưa có UC14)
CREATE TABLE IF NOT EXISTS diem_thi (
    ma_diem_thi VARCHAR(12) PRIMARY KEY,
    ten_diem_thi VARCHAR(160) NOT NULL,
    dia_chi VARCHAR(255) NOT NULL,
    suc_chua_toi_da INT NOT NULL DEFAULT 0,
    suc_chua_con_lai INT NOT NULL DEFAULT 0,
    tong_so_thi_sinh INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO diem_thi (ma_diem_thi, ten_diem_thi, dia_chi, suc_chua_toi_da, suc_chua_con_lai, tong_so_thi_sinh) VALUES
('DT01', 'THPT Nguyễn Huệ', 'Địa điểm demo số 01', 40, 40, 30),
('DT02', 'THPT Trần Phú', 'Địa điểm demo số 02', 20, 20, 15);

-- 3. BẢNG HỌC SINH / THÍ SINH (Tương thích 100% bảng hoc_sinh của hệ thống)
CREATE TABLE IF NOT EXISTS hoc_sinh (
    ma_hoc_sinh VARCHAR(12) PRIMARY KEY,
    ma_truong VARCHAR(12) NOT NULL,
    ma_diem_thi VARCHAR(12) NOT NULL,
    ho_ten VARCHAR(150) NOT NULL,
    so_bao_danh CHAR(6) UNIQUE NULL,
    trang_thai_phan_phong ENUM('ChuaPhan','DaPhan') NOT NULL DEFAULT 'ChuaPhan',
    CONSTRAINT fk_hs_thcs FOREIGN KEY (ma_truong) REFERENCES truong_thcs(ma_truong),
    CONSTRAINT fk_hs_diem FOREIGN KEY (ma_diem_thi) REFERENCES diem_thi(ma_diem_thi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO hoc_sinh (ma_hoc_sinh, ma_truong, ma_diem_thi, ho_ten) VALUES
('HS001', 'THCS01', 'DT01', 'Nguyễn Văn An'),
('HS002', 'THCS01', 'DT01', 'Trần Thị Bình'),
('HS003', 'THCS01', 'DT01', 'Lê Hoàng Chi'),
('HS004', 'THCS01', 'DT01', 'Phạm Anh Dũng'),
('HS005', 'THCS01', 'DT01', 'Võ Thị Duyên'),
('HS006', 'THCS01', 'DT01', 'Hoàng Minh Đức'),
('HS007', 'THCS01', 'DT01', 'Đặng Ngọc Hà'),
('HS008', 'THCS01', 'DT01', 'Bùi Gia Hân'),
('HS009', 'THCS01', 'DT01', 'Ngô Thanh Hải'),
('HS010', 'THCS01', 'DT01', 'Phan Thị Hương'),
('HS011', 'THCS01', 'DT01', 'Đỗ Minh Khang'),
('HS012', 'THCS01', 'DT01', 'Huỳnh Thảo Lan'),
('HS013', 'THCS01', 'DT01', 'Nguyễn Đức Long'),
('HS014', 'THCS01', 'DT01', 'Trần Mỹ Linh'),
('HS015', 'THCS01', 'DT01', 'Lê Thị Mai'),
('HS016', 'THCS02', 'DT01', 'Phạm Nhật Minh'),
('HS017', 'THCS02', 'DT01', 'Võ Hữu Nghĩa'),
('HS018', 'THCS02', 'DT01', 'Hoàng Thu Ngân'),
('HS019', 'THCS02', 'DT01', 'Đặng Kim Oanh'),
('HS020', 'THCS02', 'DT01', 'Bùi Minh Phát'),
('HS021', 'THCS02', 'DT01', 'Ngô Thị Phương'),
('HS022', 'THCS02', 'DT01', 'Phan Hoàng Quân'),
('HS023', 'THCS02', 'DT01', 'Đỗ Thị Quỳnh');

-- 4. BẢNG TRƯỜNG THPT
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

-- 5. BẢNG CHỈ TIÊU & ĐIỂM CHUẨN TRƯỜNG THPT THEO NĂM
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

-- 6. BẢNG HỒ SƠ ĐĂNG KÝ VÀ KẾT QUẢ TUYỂN SINH
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
