-- BẢN SAO LƯU DỮ LIỆU HỆ THỐNG QLTS LỚP 10
-- Thời gian tạo: 2026-10-28 17:55:30
-- Người thực hiện: admin

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `cau_hinh_he_thong`;
CREATE TABLE `cau_hinh_he_thong` (
  `khoa_cau_hinh` varchar(60) NOT NULL,
  `ten_cau_hinh` varchar(150) NOT NULL,
  `gia_tri` text NOT NULL,
  `kieu_du_lieu` enum('text','number','date','boolean') DEFAULT 'text',
  `nhom_cau_hinh` varchar(50) DEFAULT 'Tuyển sinh',
  `mo_ta` varchar(255) DEFAULT NULL,
  `ngay_cap_nhat` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`khoa_cau_hinh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `cau_hinh_he_thong` (`khoa_cau_hinh`, `ten_cau_hinh`, `gia_tri`, `kieu_du_lieu`, `nhom_cau_hinh`, `mo_ta`, `ngay_cap_nhat`) VALUES
('cho_phep_dieu_chinh_nv', 'Cho phép điều chỉnh nguyện vọng', '1', 'boolean', 'Quy tắc', '1: Đang cho phép điều chỉnh; 0: Đã khóa điều chỉnh', '2026-10-28 17:47:11'),
('nam_tuyen_sinh_hien_tai', 'Năm tuyển sinh hiện tại', '2026', 'number', 'Tuyển sinh', 'Năm học tổ chức kỳ thi tuyển sinh vào lớp 10', '2026-10-28 17:47:11'),
('so_nguyen_vong_toi_da', 'Số nguyện vọng tối đa', '3', 'number', 'Tuyển sinh', 'Số lượng nguyện vọng công lập tối đa thí sinh được đăng ký', '2026-10-28 17:47:11'),
('thoi_gian_dong_dang_ky', 'Thời gian khóa đăng ký NV', '2026-05-20 17:00:00', 'date', 'Lịch trình', 'Hạn chót thí sinh được phép nộp và điều chỉnh nguyện vọng', '2026-10-28 17:47:11'),
('thoi_gian_mo_dang_ky', 'Thời gian mở đăng ký NV', '2026-05-01 08:00:00', 'date', 'Lịch trình', 'Thời điểm thí sinh bắt đầu được đăng ký nguyện vọng', '2026-10-28 17:47:11'),
('thoi_gian_xac_nhan_nhap_hoc', 'Hạn chót xác nhận nhập học', '2026-07-15 17:00:00', 'date', 'Lịch trình', 'Hạn cuối cùng để thí sinh trúng tuyển xác nhận trực tuyến', '2026-10-28 17:47:11');

DROP TABLE IF EXISTS `chi_tieu_thpt`;
CREATE TABLE `chi_tieu_thpt` (
  `ma_chi_tieu` int(11) NOT NULL AUTO_INCREMENT,
  `ma_truong_thpt` varchar(12) NOT NULL,
  `nam_tuyen_sinh` int(11) NOT NULL,
  `tong_chi_tieu` int(11) NOT NULL DEFAULT 0,
  `chi_tieu_con_lai` int(11) NOT NULL DEFAULT 0,
  `diem_chuan_nv1` decimal(4,2) DEFAULT 0.00,
  `diem_chuan_nv2` decimal(4,2) DEFAULT 0.00,
  `diem_chuan_nv3` decimal(4,2) DEFAULT 0.00,
  PRIMARY KEY (`ma_chi_tieu`),
  UNIQUE KEY `uniq_truong_nam` (`ma_truong_thpt`,`nam_tuyen_sinh`),
  CONSTRAINT `fk_ct_truong` FOREIGN KEY (`ma_truong_thpt`) REFERENCES `truong_thpt` (`ma_truong_thpt`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `chi_tieu_thpt` (`ma_chi_tieu`, `ma_truong_thpt`, `nam_tuyen_sinh`, `tong_chi_tieu`, `chi_tieu_con_lai`, `diem_chuan_nv1`, `diem_chuan_nv2`, `diem_chuan_nv3`) VALUES
('1', 'THPT01', '2026', '450', '18', '22.50', '23.50', '24.50'),
('2', 'THPT02', '2026', '380', '24', '20.75', '21.75', '22.75'),
('3', 'THPT03', '2026', '520', '0', '24.00', '25.00', '26.00'),
('4', 'THPT01', '2025', '420', '0', '21.75', '22.75', '23.75'),
('5', 'THPT02', '2025', '360', '0', '20.00', '21.00', '22.00');

DROP TABLE IF EXISTS `diem_thi`;
CREATE TABLE `diem_thi` (
  `ma_diem_thi` varchar(12) NOT NULL,
  `ten_diem_thi` varchar(160) NOT NULL,
  `dia_chi` varchar(255) NOT NULL,
  `suc_chua_toi_da` int(11) NOT NULL DEFAULT 0,
  `suc_chua_con_lai` int(11) NOT NULL DEFAULT 0,
  `tong_so_thi_sinh` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ma_diem_thi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `diem_thi` (`ma_diem_thi`, `ten_diem_thi`, `dia_chi`, `suc_chua_toi_da`, `suc_chua_con_lai`, `tong_so_thi_sinh`) VALUES
('DT01', 'THPT Nguyễn Huệ', 'Địa điểm demo số 01', '40', '40', '30'),
('DT02', 'THPT Trần Phú', 'Địa điểm demo số 02', '20', '20', '15');

DROP TABLE IF EXISTS `ho_so_tuyen_sinh_thpt`;
CREATE TABLE `ho_so_tuyen_sinh_thpt` (
  `ma_ho_so` int(11) NOT NULL AUTO_INCREMENT,
  `ma_hoc_sinh` varchar(12) NOT NULL,
  `nam_tuyen_sinh` int(11) NOT NULL DEFAULT 2026,
  `ma_truong_thpt` varchar(12) NOT NULL,
  `nguyen_vong` int(11) NOT NULL COMMENT '1: NV1, 2: NV2, 3: NV3',
  `diem_xet_tuyen` decimal(4,2) NOT NULL DEFAULT 0.00,
  `trang_thai_ho_so` enum('HopLe','KhongHopLe') DEFAULT 'HopLe',
  `ket_qua_tuyen_sinh` enum('TrungTuyen','KhongTrungTuyen','DangXet') DEFAULT 'DangXet',
  `xac_nhan_nhap_hoc` enum('DaXacNhan','ChuaXacNhan') DEFAULT 'ChuaXacNhan',
  `thoi_gian_xac_nhan` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ma_ho_so`),
  KEY `fk_hs_thpt` (`ma_truong_thpt`),
  CONSTRAINT `fk_hs_thpt` FOREIGN KEY (`ma_truong_thpt`) REFERENCES `truong_thpt` (`ma_truong_thpt`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `ho_so_tuyen_sinh_thpt` (`ma_ho_so`, `ma_hoc_sinh`, `nam_tuyen_sinh`, `ma_truong_thpt`, `nguyen_vong`, `diem_xet_tuyen`, `trang_thai_ho_so`, `ket_qua_tuyen_sinh`, `xac_nhan_nhap_hoc`, `thoi_gian_xac_nhan`) VALUES
('1', 'HS001', '2026', 'THPT01', '1', '24.50', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 09:15:00'),
('2', 'HS002', '2026', 'THPT01', '1', '23.75', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 10:20:00'),
('3', 'HS003', '2026', 'THPT01', '1', '23.00', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 11:00:00'),
('4', 'HS004', '2026', 'THPT01', '1', '22.75', 'HopLe', 'TrungTuyen', 'ChuaXacNhan', NULL),
('5', 'HS005', '2026', 'THPT01', '1', '22.50', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-03 14:10:00'),
('6', 'HS006', '2026', 'THPT01', '1', '21.50', 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('7', 'HS007', '2026', 'THPT01', '1', '20.00', 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('8', 'HS008', '2026', 'THPT01', '2', '24.00', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-04 08:30:00'),
('9', 'HS009', '2026', 'THPT01', '2', '23.50', 'HopLe', 'TrungTuyen', 'ChuaXacNhan', NULL),
('10', 'HS010', '2026', 'THPT01', '2', '22.00', 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('11', 'HS011', '2026', 'THPT01', '3', '25.00', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-05 16:45:00'),
('12', 'HS012', '2026', 'THPT01', '3', '23.00', 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('13', 'HS013', '2026', 'THPT02', '1', '22.00', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 08:45:00'),
('14', 'HS014', '2026', 'THPT02', '1', '21.50', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 09:30:00'),
('15', 'HS015', '2026', 'THPT02', '1', '20.75', 'HopLe', 'TrungTuyen', 'ChuaXacNhan', NULL),
('16', 'HS016', '2026', 'THPT02', '1', '19.50', 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('17', 'HS017', '2026', 'THPT02', '2', '22.50', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-03 10:15:00'),
('18', 'HS018', '2026', 'THPT02', '2', '21.00', 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL),
('19', 'HS019', '2026', 'THPT02', '3', '23.00', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-04 15:00:00'),
('20', 'HS020', '2026', 'THPT03', '1', '26.50', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 14:00:00'),
('21', 'HS021', '2026', 'THPT03', '1', '25.00', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-02 15:30:00'),
('22', 'HS022', '2026', 'THPT03', '1', '24.00', 'HopLe', 'TrungTuyen', 'DaXacNhan', '2026-07-03 09:00:00'),
('23', 'HS023', '2026', 'THPT03', '1', '23.50', 'HopLe', 'KhongTrungTuyen', 'ChuaXacNhan', NULL);

DROP TABLE IF EXISTS `hoc_sinh`;
CREATE TABLE `hoc_sinh` (
  `ma_hoc_sinh` varchar(12) NOT NULL,
  `ma_truong` varchar(12) NOT NULL,
  `ma_diem_thi` varchar(12) NOT NULL,
  `ho_ten` varchar(150) NOT NULL,
  `so_bao_danh` char(6) DEFAULT NULL,
  `trang_thai_phan_phong` enum('ChuaPhan','DaPhan') NOT NULL DEFAULT 'ChuaPhan',
  PRIMARY KEY (`ma_hoc_sinh`),
  UNIQUE KEY `so_bao_danh` (`so_bao_danh`),
  KEY `fk_hocsinh_truong` (`ma_truong`),
  KEY `fk_hocsinh_diem` (`ma_diem_thi`),
  CONSTRAINT `fk_hocsinh_diem` FOREIGN KEY (`ma_diem_thi`) REFERENCES `diem_thi` (`ma_diem_thi`),
  CONSTRAINT `fk_hocsinh_truong` FOREIGN KEY (`ma_truong`) REFERENCES `truong_thcs` (`ma_truong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `hoc_sinh` (`ma_hoc_sinh`, `ma_truong`, `ma_diem_thi`, `ho_ten`, `so_bao_danh`, `trang_thai_phan_phong`) VALUES
('HS001', 'THCS01', 'DT01', 'Nguyễn Văn An', NULL, 'ChuaPhan'),
('HS002', 'THCS01', 'DT01', 'Trần Thị Bình', NULL, 'ChuaPhan'),
('HS003', 'THCS01', 'DT01', 'Lê Hoàng Chi', NULL, 'ChuaPhan'),
('HS004', 'THCS01', 'DT01', 'Phạm Anh Dũng', NULL, 'ChuaPhan'),
('HS005', 'THCS01', 'DT01', 'Võ Thị Duyên', NULL, 'ChuaPhan'),
('HS006', 'THCS01', 'DT01', 'Hoàng Minh Đức', NULL, 'ChuaPhan'),
('HS007', 'THCS01', 'DT01', 'Đặng Ngọc Hà', NULL, 'ChuaPhan'),
('HS008', 'THCS01', 'DT01', 'Bùi Gia Hân', NULL, 'ChuaPhan'),
('HS009', 'THCS01', 'DT01', 'Ngô Thanh Hải', NULL, 'ChuaPhan'),
('HS010', 'THCS01', 'DT01', 'Phan Thị Hương', NULL, 'ChuaPhan'),
('HS011', 'THCS01', 'DT01', 'Đỗ Minh Khang', NULL, 'ChuaPhan'),
('HS012', 'THCS01', 'DT01', 'Huỳnh Thảo Lan', NULL, 'ChuaPhan'),
('HS013', 'THCS01', 'DT01', 'Nguyễn Đức Long', NULL, 'ChuaPhan'),
('HS014', 'THCS01', 'DT01', 'Trần Mỹ Linh', NULL, 'ChuaPhan'),
('HS015', 'THCS01', 'DT01', 'Lê Thị Mai', NULL, 'ChuaPhan'),
('HS016', 'THCS02', 'DT01', 'Phạm Nhật Minh', NULL, 'ChuaPhan'),
('HS017', 'THCS02', 'DT01', 'Võ Hữu Nghĩa', NULL, 'ChuaPhan'),
('HS018', 'THCS02', 'DT01', 'Hoàng Thu Ngân', NULL, 'ChuaPhan'),
('HS019', 'THCS02', 'DT01', 'Đặng Kim Oanh', NULL, 'ChuaPhan'),
('HS020', 'THCS02', 'DT01', 'Bùi Minh Phát', NULL, 'ChuaPhan'),
('HS021', 'THCS02', 'DT01', 'Ngô Thị Phương', NULL, 'ChuaPhan'),
('HS022', 'THCS02', 'DT01', 'Phan Hoàng Quân', NULL, 'ChuaPhan'),
('HS023', 'THCS02', 'DT01', 'Đỗ Thị Quỳnh', NULL, 'ChuaPhan'),
('HS024', 'THCS02', 'DT01', 'Huỳnh Bảo Sơn', NULL, 'ChuaPhan'),
('HS025', 'THCS02', 'DT01', 'Nguyễn Khánh Tâm', NULL, 'ChuaPhan'),
('HS026', 'THCS02', 'DT01', 'Trần Quốc Thắng', NULL, 'ChuaPhan'),
('HS027', 'THCS02', 'DT01', 'Lê Thanh Thảo', NULL, 'ChuaPhan'),
('HS028', 'THCS02', 'DT01', 'Phạm Minh Thư', NULL, 'ChuaPhan'),
('HS029', 'THCS02', 'DT01', 'Võ Ngọc Trâm', NULL, 'ChuaPhan'),
('HS030', 'THCS02', 'DT01', 'Hoàng Gia Tuấn', NULL, 'ChuaPhan'),
('HS031', 'THCS03', 'DT02', 'Đặng Kim Uyên', NULL, 'ChuaPhan'),
('HS032', 'THCS03', 'DT02', 'Bùi Văn Việt', NULL, 'ChuaPhan'),
('HS033', 'THCS03', 'DT02', 'Ngô Quốc Vinh', NULL, 'ChuaPhan'),
('HS034', 'THCS03', 'DT02', 'Phan Minh Vy', NULL, 'ChuaPhan'),
('HS035', 'THCS03', 'DT02', 'Đỗ Phương Yến', NULL, 'ChuaPhan'),
('HS036', 'THCS03', 'DT02', 'Huỳnh Nhật Anh', NULL, 'ChuaPhan'),
('HS037', 'THCS03', 'DT02', 'Nguyễn Ngọc Bảo', NULL, 'ChuaPhan'),
('HS038', 'THCS03', 'DT02', 'Trần Thanh Cường', NULL, 'ChuaPhan'),
('HS039', 'THCS03', 'DT02', 'Lê Minh Châu', NULL, 'ChuaPhan'),
('HS040', 'THCS03', 'DT02', 'Phạm Hoàng Đạt', NULL, 'ChuaPhan'),
('HS041', 'THCS03', 'DT02', 'Võ Thu Hằng', NULL, 'ChuaPhan'),
('HS042', 'THCS03', 'DT02', 'Hoàng Khánh Huyền', NULL, 'ChuaPhan'),
('HS043', 'THCS03', 'DT02', 'Đặng Hữu Lộc', NULL, 'ChuaPhan'),
('HS044', 'THCS03', 'DT02', 'Bùi Thị Ngọc', NULL, 'ChuaPhan'),
('HS045', 'THCS03', 'DT02', 'Ngô Gia Phúc', NULL, 'ChuaPhan');

DROP TABLE IF EXISTS `ket_qua_phan_diem_thi`;
CREATE TABLE `ket_qua_phan_diem_thi` (
  `ma_ket_qua_phan_diem` int(11) NOT NULL AUTO_INCREMENT,
  `ma_truong` varchar(12) NOT NULL,
  `ma_diem_thi` varchar(12) NOT NULL,
  `so_luong_thi_sinh` int(11) NOT NULL,
  `trang_thai_phan_diem` enum('ChuaPhan','DaPhan') NOT NULL DEFAULT 'DaPhan',
  PRIMARY KEY (`ma_ket_qua_phan_diem`),
  UNIQUE KEY `uniq_truong_diem` (`ma_truong`,`ma_diem_thi`),
  KEY `fk_phan_diem_diem` (`ma_diem_thi`),
  CONSTRAINT `fk_phan_diem_diem` FOREIGN KEY (`ma_diem_thi`) REFERENCES `diem_thi` (`ma_diem_thi`),
  CONSTRAINT `fk_phan_diem_truong` FOREIGN KEY (`ma_truong`) REFERENCES `truong_thcs` (`ma_truong`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `ket_qua_phan_diem_thi` (`ma_ket_qua_phan_diem`, `ma_truong`, `ma_diem_thi`, `so_luong_thi_sinh`, `trang_thai_phan_diem`) VALUES
('1', 'THCS01', 'DT01', '15', 'DaPhan'),
('2', 'THCS02', 'DT01', '15', 'DaPhan'),
('3', 'THCS03', 'DT02', '15', 'DaPhan');

DROP TABLE IF EXISTS `ket_qua_phan_phong_thi`;
CREATE TABLE `ket_qua_phan_phong_thi` (
  `ma_ket_qua_phan_phong` int(11) NOT NULL AUTO_INCREMENT,
  `ma_hoc_sinh` varchar(12) NOT NULL,
  `ma_diem_thi` varchar(12) NOT NULL,
  `ma_phong_thi` varchar(12) NOT NULL,
  `so_bao_danh` char(6) NOT NULL,
  `trang_thai_phan_phong` enum('ChuaPhan','DaPhan') NOT NULL DEFAULT 'DaPhan',
  `thoi_gian_xac_nhan` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_ket_qua_phan_phong`),
  UNIQUE KEY `ma_hoc_sinh` (`ma_hoc_sinh`),
  UNIQUE KEY `so_bao_danh` (`so_bao_danh`),
  KEY `fk_phan_phong_diem` (`ma_diem_thi`),
  KEY `fk_phan_phong_phong` (`ma_phong_thi`),
  CONSTRAINT `fk_phan_phong_diem` FOREIGN KEY (`ma_diem_thi`) REFERENCES `diem_thi` (`ma_diem_thi`),
  CONSTRAINT `fk_phan_phong_hs` FOREIGN KEY (`ma_hoc_sinh`) REFERENCES `hoc_sinh` (`ma_hoc_sinh`),
  CONSTRAINT `fk_phan_phong_phong` FOREIGN KEY (`ma_phong_thi`) REFERENCES `phong_thi` (`ma_phong_thi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

DROP TABLE IF EXISTS `nguoi_dung`;
CREATE TABLE `nguoi_dung` (
  `ma_nguoi_dung` int(11) NOT NULL AUTO_INCREMENT,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `vai_tro` enum('QuanTriVien','CanBoSo','BanTuyenSinhTHPT','GiaoVuTHCS','ThuKy','HocSinh') NOT NULL DEFAULT 'CanBoSo',
  `trang_thai` enum('HoatDong','DaKhoa') NOT NULL DEFAULT 'HoatDong',
  `quyen_han` text DEFAULT NULL,
  `ngay_tao` timestamp NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ma_nguoi_dung`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `nguoi_dung` (`ma_nguoi_dung`, `ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `so_dien_thoai`, `vai_tro`, `trang_thai`, `quyen_han`, `ngay_tao`, `ngay_cap_nhat`) VALUES
('1', 'admin', '123456', 'Quản trị viên Hệ thống', 'admin@tuyensinh10.edu.vn', '0901234567', 'QuanTriVien', 'HoatDong', 'ALL', '2026-10-28 17:47:11', '2026-10-28 17:47:11'),
('2', 'canbo', 'Demo@123', 'Nguyễn Văn Cán Bộ', 'canbo@sogddt.edu.vn', '0912345678', 'CanBoSo', 'HoatDong', 'UC14,UC09', '2026-10-28 17:47:11', '2026-10-28 17:47:11'),
('3', 'bantuyensinh_nh', '123456', 'Ban Tuyển Sinh THPT Nguyễn Huệ', 'tuyensinh.nguyenhue@hcm.edu.vn', '0923456789', 'BanTuyenSinhTHPT', 'HoatDong', 'UC09', '2026-10-28 17:47:11', '2026-10-28 17:47:11'),
('4', 'bantuyensinh_tp', '123456', 'Ban Tuyển Sinh THPT Trần Phú', 'tuyensinh.tranphu@hcm.edu.vn', '0934567890', 'BanTuyenSinhTHPT', 'HoatDong', 'UC09', '2026-10-28 17:47:11', '2026-10-28 17:47:11'),
('5', 'giaovu_nd', '123456', 'Giáo vụ THCS Nguyễn Du', 'giaovu.nguyendu@hcm.edu.vn', '0945678901', 'GiaoVuTHCS', 'HoatDong', 'UC07', '2026-10-28 17:47:11', '2026-10-28 17:47:11'),
('6', 'thuky01', '123456', 'Trần Thị Thư Ký', 'thuky@sogddt.edu.vn', '0956789012', 'ThuKy', 'HoatDong', 'UC19,UC20', '2026-10-28 17:47:11', '2026-10-28 17:47:11'),
('7', 'hocsinh01', '123456', 'Nguyễn Văn An', 'hs.an@gmail.com', '0967890123', 'HocSinh', 'HoatDong', 'UC01,UC04,UC05,UC06', '2026-10-28 17:47:11', '2026-10-28 17:47:11');

DROP TABLE IF EXISTS `nhat_ky_he_thong`;
CREATE TABLE `nhat_ky_he_thong` (
  `ma_nhat_ky` int(11) NOT NULL AUTO_INCREMENT,
  `loai_nhat_ky` enum('DangNhap','QuanTri','HeThong','SaoLuu','Loi','BaoMat') NOT NULL DEFAULT 'HeThong',
  `nguoi_thuc_hien` varchar(50) NOT NULL DEFAULT 'Hệ thống',
  `hanh_dong` varchar(255) NOT NULL,
  `chi_tiet` text DEFAULT NULL,
  `dia_chi_ip` varchar(45) DEFAULT '127.0.0.1',
  `thoi_gian` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ma_nhat_ky`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `nhat_ky_he_thong` (`ma_nhat_ky`, `loai_nhat_ky`, `nguoi_thuc_hien`, `hanh_dong`, `chi_tiet`, `dia_chi_ip`, `thoi_gian`) VALUES
('1', 'DangNhap', 'admin', 'Đăng nhập hệ thống thành công', 'IP: 127.0.0.1, Trình duyệt: Chrome/Windows', '127.0.0.1', '2026-10-28 17:47:11'),
('2', 'QuanTri', 'admin', 'Cập nhật tài khoản người dùng', 'Cập nhật quyền hạn cho tài khoản canbo', '127.0.0.1', '2026-10-28 17:47:11'),
('3', 'HeThong', 'admin', 'Lưu cấu hình hệ thống', 'Cập nhật tham số: nam_tuyen_sinh_hien_tai = 2026', '127.0.0.1', '2026-10-28 17:47:11'),
('4', 'SaoLuu', 'admin', 'Tạo bản sao lưu dữ liệu tự động', 'File: backup_20261028_230000.sql, Dung lượng: 450 KB', '127.0.0.1', '2026-10-28 17:47:11'),
('5', 'QuanTri', 'admin', 'Tạo tài khoản mới', 'Tạo người dùng: test_user_1793210130 (Vai trò: CanBoSo)', '127.0.0.1', '2026-10-28 17:55:30'),
('6', 'QuanTri', 'admin', 'Cập nhật tài khoản', 'Cập nhật thông tin tài khoản ID #8', '127.0.0.1', '2026-10-28 17:55:30'),
('7', 'QuanTri', 'admin', 'Khóa tài khoản', 'Khóa tài khoản: test_user_1793210130 (ID #8)', '127.0.0.1', '2026-10-28 17:55:30'),
('8', 'QuanTri', 'admin', 'Mở khóa tài khoản', 'Mở khóa tài khoản: test_user_1793210130 (ID #8)', '127.0.0.1', '2026-10-28 17:55:30'),
('9', 'HeThong', 'admin', 'Cập nhật cấu hình', 'Thay đổi giá trị tham số [nam_tuyen_sinh_hien_tai] thành \'2026\'', '127.0.0.1', '2026-10-28 17:55:30');

DROP TABLE IF EXISTS `phong_thi`;
CREATE TABLE `phong_thi` (
  `ma_phong_thi` varchar(12) NOT NULL,
  `ma_diem_thi` varchar(12) NOT NULL,
  `so_phong` varchar(30) NOT NULL,
  `suc_chua` int(11) NOT NULL,
  `so_luong_thi_sinh_hien_tai` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ma_phong_thi`),
  UNIQUE KEY `uniq_so_phong` (`ma_diem_thi`,`so_phong`),
  CONSTRAINT `fk_phong_diem` FOREIGN KEY (`ma_diem_thi`) REFERENCES `diem_thi` (`ma_diem_thi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `phong_thi` (`ma_phong_thi`, `ma_diem_thi`, `so_phong`, `suc_chua`, `so_luong_thi_sinh_hien_tai`) VALUES
('P0101', 'DT01', 'Phòng 01', '20', '0'),
('P0102', 'DT01', 'Phòng 02', '20', '0'),
('P0201', 'DT02', 'Phòng 01', '20', '0');

DROP TABLE IF EXISTS `truong_thcs`;
CREATE TABLE `truong_thcs` (
  `ma_truong` varchar(12) NOT NULL,
  `ten_truong` varchar(160) NOT NULL,
  PRIMARY KEY (`ma_truong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `truong_thcs` (`ma_truong`, `ten_truong`) VALUES
('THCS01', 'THCS Nguyễn Du'),
('THCS02', 'THCS Lê Quý Đôn'),
('THCS03', 'THCS Nguyễn Trãi');

DROP TABLE IF EXISTS `truong_thpt`;
CREATE TABLE `truong_thpt` (
  `ma_truong_thpt` varchar(12) NOT NULL,
  `ten_truong` varchar(160) NOT NULL,
  `dia_chi` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ma_truong_thpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `truong_thpt` (`ma_truong_thpt`, `ten_truong`, `dia_chi`, `so_dien_thoai`) VALUES
('THPT01', 'THPT Nguyễn Huệ', 'Số 123 Quang Trung, Quận Gò Vấp, TP.HCM', '02838941234'),
('THPT02', 'THPT Trần Phú', 'Số 45 Lê Thúc Hoạch, Quận Tân Phú, TP.HCM', '02838604321'),
('THPT03', 'THPT Gia Định', 'Số 195/29 Xô Viết Nghệ Tĩnh, Quận Bình Thạnh, TP.HCM', '02838991234');

SET FOREIGN_KEY_CHECKS=1;
