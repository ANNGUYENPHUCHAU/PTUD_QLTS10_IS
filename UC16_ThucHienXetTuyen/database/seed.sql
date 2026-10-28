INSERT INTO admission_rounds (id, name, school_year, starts_at, ends_at, status)
VALUES (1, 'Đợt xét tuyển chính thức', '2026-2027', '2020-01-01 00:00:00', '2099-12-31 23:59:59', 'active');

INSERT INTO schools (id, code, name, quota) VALUES
    (1, 'THPT-NTT', 'THPT Nguyễn Trãi', 3),
    (2, 'THPT-LQD', 'THPT Lê Quý Đôn', 3),
    (3, 'THPT-PBC', 'THPT Phan Bội Châu', 7);

INSERT INTO students (id, candidate_number, full_name, total_score) VALUES
    (1, 'TS001', 'Nguyễn Minh Anh', 28.50),
    (2, 'TS002', 'Trần Hải Nam', 27.75),
    (3, 'TS003', 'Lê Thu Hà', 27.00),
    (4, 'TS004', 'Phạm Đức Long', 26.50),
    (5, 'TS005', 'Hoàng Ngọc Mai', 26.00),
    (6, 'TS006', 'Vũ Quang Huy', 25.75),
    (7, 'TS007', 'Đỗ Khánh Linh', 25.25),
    (8, 'TS008', 'Bùi Anh Tuấn', 24.50),
    (9, 'TS009', 'Đặng Thảo My', 24.00),
    (10, 'TS010', 'Ngô Gia Bảo', 23.50),
    (11, 'TS011', 'Dương Hà Vy', 22.75),
    (12, 'TS012', 'Lý Minh Khang', 22.00);

INSERT INTO applications (round_id, student_id, school_id, priority) VALUES
    (1, 1, 1, 1), (1, 1, 2, 2), (1, 1, 3, 3),
    (1, 2, 1, 1), (1, 2, 2, 2), (1, 2, 3, 3),
    (1, 3, 1, 1), (1, 3, 3, 2), (1, 3, 2, 3),
    (1, 4, 1, 1), (1, 4, 2, 2), (1, 4, 3, 3),
    (1, 5, 1, 1), (1, 5, 2, 2), (1, 5, 3, 3),
    (1, 6, 2, 1), (1, 6, 1, 2), (1, 6, 3, 3),
    (1, 7, 2, 1), (1, 7, 3, 2), (1, 7, 1, 3),
    (1, 8, 2, 1), (1, 8, 3, 2), (1, 8, 1, 3),
    (1, 9, 2, 1), (1, 9, 3, 2), (1, 9, 1, 3),
    (1, 10, 3, 1), (1, 10, 2, 2), (1, 10, 1, 3),
    (1, 11, 3, 1), (1, 11, 1, 2), (1, 11, 2, 3),
    (1, 12, 3, 1), (1, 12, 1, 2), (1, 12, 2, 3);

