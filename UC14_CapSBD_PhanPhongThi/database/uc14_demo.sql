-- UC14: CSDL demo RIÊNG, không đụng tới các database khác trên máy.
-- Chạy bằng phpMyAdmin -> Import (tài liệu demo, dữ liệu giả lập).
CREATE DATABASE IF NOT EXISTS uc14_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uc14_demo;

CREATE TABLE IF NOT EXISTS truong_thcs (
 ma_truong VARCHAR(12) PRIMARY KEY,
 ten_truong VARCHAR(160) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS diem_thi (
 ma_diem_thi VARCHAR(12) PRIMARY KEY,
 ten_diem_thi VARCHAR(160) NOT NULL,
 dia_chi VARCHAR(255) NOT NULL,
 suc_chua_toi_da INT NOT NULL DEFAULT 0,
 suc_chua_con_lai INT NOT NULL DEFAULT 0,
 tong_so_thi_sinh INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS phong_thi (
 ma_phong_thi VARCHAR(12) PRIMARY KEY,
 ma_diem_thi VARCHAR(12) NOT NULL,
 so_phong VARCHAR(30) NOT NULL,
 suc_chua INT NOT NULL,
 so_luong_thi_sinh_hien_tai INT NOT NULL DEFAULT 0,
 CONSTRAINT fk_phong_diem FOREIGN KEY (ma_diem_thi) REFERENCES diem_thi(ma_diem_thi),
 UNIQUE KEY uniq_so_phong (ma_diem_thi, so_phong)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hoc_sinh (
 ma_hoc_sinh VARCHAR(12) PRIMARY KEY,
 ma_truong VARCHAR(12) NOT NULL,
 ma_diem_thi VARCHAR(12) NOT NULL,
 ho_ten VARCHAR(150) NOT NULL,
 so_bao_danh CHAR(6) UNIQUE NULL,
 trang_thai_phan_phong ENUM('ChuaPhan','DaPhan') NOT NULL DEFAULT 'ChuaPhan',
 CONSTRAINT fk_hocsinh_truong FOREIGN KEY (ma_truong) REFERENCES truong_thcs(ma_truong),
 CONSTRAINT fk_hocsinh_diem FOREIGN KEY (ma_diem_thi) REFERENCES diem_thi(ma_diem_thi)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ket_qua_phan_diem_thi (
 ma_ket_qua_phan_diem INT AUTO_INCREMENT PRIMARY KEY,
 ma_truong VARCHAR(12) NOT NULL,
 ma_diem_thi VARCHAR(12) NOT NULL,
 so_luong_thi_sinh INT NOT NULL,
 trang_thai_phan_diem ENUM('ChuaPhan','DaPhan') NOT NULL DEFAULT 'DaPhan',
 UNIQUE KEY uniq_truong_diem (ma_truong, ma_diem_thi),
 CONSTRAINT fk_phan_diem_truong FOREIGN KEY (ma_truong) REFERENCES truong_thcs(ma_truong),
 CONSTRAINT fk_phan_diem_diem FOREIGN KEY (ma_diem_thi) REFERENCES diem_thi(ma_diem_thi)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ket_qua_phan_phong_thi (
 ma_ket_qua_phan_phong INT AUTO_INCREMENT PRIMARY KEY,
 ma_hoc_sinh VARCHAR(12) NOT NULL UNIQUE,
 ma_diem_thi VARCHAR(12) NOT NULL,
 ma_phong_thi VARCHAR(12) NOT NULL,
 so_bao_danh CHAR(6) NOT NULL UNIQUE,
 trang_thai_phan_phong ENUM('ChuaPhan','DaPhan') NOT NULL DEFAULT 'DaPhan',
 thoi_gian_xac_nhan TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_phan_phong_hs FOREIGN KEY (ma_hoc_sinh) REFERENCES hoc_sinh(ma_hoc_sinh),
 CONSTRAINT fk_phan_phong_diem FOREIGN KEY (ma_diem_thi) REFERENCES diem_thi(ma_diem_thi),
 CONSTRAINT fk_phan_phong_phong FOREIGN KEY (ma_phong_thi) REFERENCES phong_thi(ma_phong_thi)
) ENGINE=InnoDB;

INSERT IGNORE INTO truong_thcs(ma_truong,ten_truong) VALUES
('THCS01','THCS Nguyễn Du'),
('THCS02','THCS Lê Quý Đôn'),
('THCS03','THCS Nguyễn Trãi');

INSERT IGNORE INTO diem_thi(ma_diem_thi,ten_diem_thi,dia_chi,suc_chua_toi_da,suc_chua_con_lai,tong_so_thi_sinh) VALUES
('DT01','THPT Nguyễn Huệ','Địa điểm demo số 01',40,40,30),
('DT02','THPT Trần Phú','Địa điểm demo số 02',20,20,15);

INSERT IGNORE INTO phong_thi(ma_phong_thi,ma_diem_thi,so_phong,suc_chua) VALUES
('P0101','DT01','Phòng 01',20),
('P0102','DT01','Phòng 02',20),
('P0201','DT02','Phòng 01',20);

-- Phân điểm thi đã thực hiện trước UC14.
INSERT IGNORE INTO ket_qua_phan_diem_thi(ma_truong,ma_diem_thi,so_luong_thi_sinh,trang_thai_phan_diem) VALUES
('THCS01','DT01',15,'DaPhan'),
('THCS02','DT01',15,'DaPhan'),
('THCS03','DT02',15,'DaPhan');

-- Danh sách 45 học sinh GIẢ LẬP, không phải dữ liệu cá nhân thật.
INSERT IGNORE INTO hoc_sinh (ma_hoc_sinh,ma_truong,ma_diem_thi,ho_ten) VALUES

('HS001','THCS01','DT01','Nguyễn Văn An'),
('HS002','THCS01','DT01','Trần Thị Bình'),
('HS003','THCS01','DT01','Lê Hoàng Chi'),
('HS004','THCS01','DT01','Phạm Anh Dũng'),
('HS005','THCS01','DT01','Võ Thị Duyên'),
('HS006','THCS01','DT01','Hoàng Minh Đức'),
('HS007','THCS01','DT01','Đặng Ngọc Hà'),
('HS008','THCS01','DT01','Bùi Gia Hân'),
('HS009','THCS01','DT01','Ngô Thanh Hải'),
('HS010','THCS01','DT01','Phan Thị Hương'),
('HS011','THCS01','DT01','Đỗ Minh Khang'),
('HS012','THCS01','DT01','Huỳnh Thảo Lan'),
('HS013','THCS01','DT01','Nguyễn Đức Long'),
('HS014','THCS01','DT01','Trần Mỹ Linh'),
('HS015','THCS01','DT01','Lê Thị Mai'),
('HS016','THCS02','DT01','Phạm Nhật Minh'),
('HS017','THCS02','DT01','Võ Hữu Nghĩa'),
('HS018','THCS02','DT01','Hoàng Thu Ngân'),
('HS019','THCS02','DT01','Đặng Kim Oanh'),
('HS020','THCS02','DT01','Bùi Minh Phát'),
('HS021','THCS02','DT01','Ngô Thị Phương'),
('HS022','THCS02','DT01','Phan Hoàng Quân'),
('HS023','THCS02','DT01','Đỗ Thị Quỳnh'),
('HS024','THCS02','DT01','Huỳnh Bảo Sơn'),
('HS025','THCS02','DT01','Nguyễn Khánh Tâm'),
('HS026','THCS02','DT01','Trần Quốc Thắng'),
('HS027','THCS02','DT01','Lê Thanh Thảo'),
('HS028','THCS02','DT01','Phạm Minh Thư'),
('HS029','THCS02','DT01','Võ Ngọc Trâm'),
('HS030','THCS02','DT01','Hoàng Gia Tuấn'),
('HS031','THCS03','DT02','Đặng Kim Uyên'),
('HS032','THCS03','DT02','Bùi Văn Việt'),
('HS033','THCS03','DT02','Ngô Quốc Vinh'),
('HS034','THCS03','DT02','Phan Minh Vy'),
('HS035','THCS03','DT02','Đỗ Phương Yến'),
('HS036','THCS03','DT02','Huỳnh Nhật Anh'),
('HS037','THCS03','DT02','Nguyễn Ngọc Bảo'),
('HS038','THCS03','DT02','Trần Thanh Cường'),
('HS039','THCS03','DT02','Lê Minh Châu'),
('HS040','THCS03','DT02','Phạm Hoàng Đạt'),
('HS041','THCS03','DT02','Võ Thu Hằng'),
('HS042','THCS03','DT02','Hoàng Khánh Huyền'),
('HS043','THCS03','DT02','Đặng Hữu Lộc'),
('HS044','THCS03','DT02','Bùi Thị Ngọc'),
('HS045','THCS03','DT02','Ngô Gia Phúc');

-- NOTE: Dùng nút 'Đặt lại dữ liệu demo' để xóa kết quả phân phòng, KHÔNG cần Import lại.
