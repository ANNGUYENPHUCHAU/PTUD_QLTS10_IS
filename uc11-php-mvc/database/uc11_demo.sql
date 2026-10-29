-- UC11: lược đồ SQLite. Ứng dụng tự thực thi khi khởi động.
-- Không import tệp này vào phpMyAdmin/MySQL vì cú pháp dành cho SQLite.
CREATE TABLE IF NOT EXISTS rounds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    opens TEXT NOT NULL,
    closes TEXT NOT NULL,
    selection TEXT NOT NULL,
    confirmation TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Chưa mở',
    version INTEGER NOT NULL DEFAULT 1
);
CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    round_id INTEGER,
    action TEXT NOT NULL,
    actor TEXT NOT NULL,
    occurred_at TEXT NOT NULL
);
