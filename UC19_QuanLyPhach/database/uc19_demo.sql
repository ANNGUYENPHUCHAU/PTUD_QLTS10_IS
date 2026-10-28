-- UC19 mới: túi chấm sau dồn túi.
CREATE DATABASE uc19_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uc19_demo;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY, username VARCHAR(50) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL, role VARCHAR(30) NOT NULL,
    can_anonymize INTEGER NOT NULL DEFAULT 0, can_view_mapping INTEGER NOT NULL DEFAULT 0,
    active INTEGER NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS exams (
    id INTEGER PRIMARY KEY, name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY, name VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL,
    CHECK (type IN ('essay','multiple_choice'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS exam_subjects (
    id INTEGER PRIMARY KEY, exam_id INTEGER NOT NULL, subject_id INTEGER NOT NULL,
    UNIQUE (exam_id,subject_id),
    FOREIGN KEY (exam_id) REFERENCES exams(id), FOREIGN KEY (subject_id) REFERENCES subjects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS rooms (
    id INTEGER PRIMARY KEY, name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY, code VARCHAR(30) NOT NULL UNIQUE, full_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS exam_candidates (
    scope_id INTEGER NOT NULL, student_id INTEGER NOT NULL, room_id INTEGER NOT NULL,
    candidate_number VARCHAR(30) NULL, attendance VARCHAR(20) NOT NULL,
    PRIMARY KEY (scope_id,student_id), UNIQUE (scope_id,candidate_number),
    FOREIGN KEY (scope_id) REFERENCES exam_subjects(id),
    FOREIGN KEY (student_id) REFERENCES students(id), FOREIGN KEY (room_id) REFERENCES rooms(id),
    CHECK (attendance IN ('present','absent'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- NULL cho dữ liệu bàn giao thiếu cần kiểm tra; UC19 từ chối trước khi sinh phách.
CREATE TABLE IF NOT EXISTS grading_bags (
    id INTEGER PRIMARY KEY, scope_id INTEGER NOT NULL, code VARCHAR(30) NOT NULL UNIQUE,
    paper_count INTEGER NULL, sheet_count INTEGER NULL, handover_ref VARCHAR(100) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending', created_by INTEGER NULL, created_at DATETIME NULL,
    FOREIGN KEY (scope_id) REFERENCES exam_subjects(id), FOREIGN KEY (created_by) REFERENCES users(id),
    CHECK (status IN ('pending','created')),
    CHECK (paper_count IS NULL OR paper_count>=0), CHECK (sheet_count IS NULL OR sheet_count>=0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Dữ liệu bàn giao theo thí sinh; id chỉ là khóa dòng bàn giao, không phải mã bài thi.
CREATE TABLE IF NOT EXISTS handover_entries (
    id INTEGER PRIMARY KEY, bag_id INTEGER NOT NULL, scope_id INTEGER NOT NULL,
    student_id INTEGER NULL, room_id INTEGER NULL, sheet_count INTEGER NULL,
    FOREIGN KEY (bag_id) REFERENCES grading_bags(id),
    FOREIGN KEY (scope_id) REFERENCES exam_subjects(id),
    FOREIGN KEY (student_id) REFERENCES students(id), FOREIGN KEY (room_id) REFERENCES rooms(id),
    CHECK (sheet_count IS NULL OR sheet_count>=1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Chỉ tạo bản ghi bài thi khi xác nhận lưu phách. Không có mã bài thi riêng.
CREATE TABLE IF NOT EXISTS papers (
    handover_id INTEGER NOT NULL UNIQUE, scope_id INTEGER NOT NULL, bag_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL, number VARCHAR(9) NOT NULL, sheet_count INTEGER NOT NULL,
    PRIMARY KEY (scope_id,number), UNIQUE (scope_id,student_id),
    FOREIGN KEY (handover_id) REFERENCES handover_entries(id),
    FOREIGN KEY (scope_id) REFERENCES exam_subjects(id),
    FOREIGN KEY (bag_id) REFERENCES grading_bags(id), FOREIGN KEY (student_id) REFERENCES students(id),
    CHECK (sheet_count>=1), CHECK (LENGTH(number)=9)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTO_INCREMENT, bag_id INTEGER NOT NULL, user_id INTEGER NOT NULL,
    action VARCHAR(30) NOT NULL, paper_count INTEGER NOT NULL, sheet_count INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (bag_id) REFERENCES grading_bags(id), FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tất cả tài khoản demo dùng mật khẩu ThuKy@123.
INSERT IGNORE INTO users VALUES
(1,'thuky','$2y$10$ohbTcxnxjbrzFgyuIdihiOb9gXAste9F6hHF/v63i2b1xSd0sWTFq','Lê Thanh Bách','secretary',1,1,1),
(2,'khongquyen','$2y$10$ohbTcxnxjbrzFgyuIdihiOb9gXAste9F6hHF/v63i2b1xSd0sWTFq','Không có quyền quản lý phách','secretary',0,0,1),
(3,'khongdoichieu','$2y$10$ohbTcxnxjbrzFgyuIdihiOb9gXAste9F6hHF/v63i2b1xSd0sWTFq','Không có quyền đối chiếu','secretary',1,0,1);
INSERT IGNORE INTO exams VALUES (1,'Tuyển sinh lớp 10 · 2026'),(2,'Tuyển sinh lớp 10 · 2027');
INSERT IGNORE INTO subjects VALUES (1,'Toán','essay'),(2,'Ngữ văn','essay'),(3,'Tiếng Anh','multiple_choice');
INSERT IGNORE INTO exam_subjects VALUES (1,1,1),(2,1,2),(3,2,1),(4,1,3);
INSERT IGNORE INTO rooms VALUES (1,'P.101'),(2,'P.102'),(3,'P.201');
INSERT IGNORE INTO students VALUES
(1,'HS001','Nguyễn Minh Anh'),(2,'HS002','Trần Hoàng Bảo'),(3,'HS003','Lê Ngọc Châu'),
(4,'HS004','Phạm Tiến Dũng'),(5,'HS005','Võ Khánh Hà'),(6,'HS006','Đặng Gia Huy'),
(7,'HS007','Nguyễn Minh Khang'),(8,'HS008','Trần Thanh Lan'),(9,'HS009','Lê Anh Minh'),
(10,'HS010','Phạm Bảo Ngọc'),(11,'HS011','Võ Thu Phương'),(12,'HS012','Đặng Minh Quân');
INSERT IGNORE INTO exam_candidates VALUES
(1,1,1,'260001','present'),(1,2,2,'260002','present'),(1,3,3,'260003','present'),
(1,4,1,'260004','present'),(1,5,2,'260005','present'),(1,6,1,'260006','present'),
(1,7,2,'260007','present'),(1,8,1,'260008','present'),(1,9,2,'260009','present'),
(1,10,1,'260010','present'),(1,11,2,'260011','present'),(1,12,3,'260012','present'),
(2,1,1,'260001','present'),(2,2,2,'260002','present');
INSERT IGNORE INTO grading_bags (id,scope_id,code,paper_count,sheet_count,handover_ref) VALUES
(1,1,'TUI-T01',3,6,'BB-2026-T01'),(2,1,'TUI-T02',2,3,'BB-2026-T02'),
(3,1,'TUI-T03',0,0,'BB-2026-T03'),(4,1,'TUI-T04',3,4,'BB-2026-T04'),
(5,1,'TUI-T05',2,7,'BB-2026-T05'),(6,1,'TUI-T06',1,1,NULL),
(7,1,'TUI-T07',1,1,'BB-2026-T07'),(8,1,'TUI-T08',1,1,'BB-2026-T08'),
(9,1,'TUI-T09',1,1,'BB-2026-T09'),(10,1,'TUI-T10',1,1,'BB-2026-T10'),
(11,2,'TUI-V01',2,4,'BB-2026-V01');
INSERT IGNORE INTO handover_entries VALUES
(1,1,1,1,1,2),(2,1,1,2,2,1),(3,1,1,3,3,3),
(4,2,1,4,1,1),(5,2,1,5,2,2),
(6,4,1,6,1,2),(7,4,1,7,2,2),(8,5,1,8,1,2),(9,5,1,9,2,2),
(10,6,1,10,1,1),(11,7,1,11,2,1),(12,9,1,12,3,NULL),
(13,10,1,NULL,1,1),(14,11,2,1,1,2),(15,11,2,2,2,2),
(16,8,1,11,2,1);
