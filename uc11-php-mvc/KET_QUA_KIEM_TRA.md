# Kết quả kiểm tra ngày 28/10/2026

Môi trường kiểm tra: PHP CLI 8.3.6, PDO SQLite, múi giờ Asia/Ho_Chi_Minh.

- Kiểm tra cú pháp toàn bộ 13 tệp PHP: đạt.
- Chạy tests/test_uc11_algorithm.php: 18 kiểm tra đạt.
- Kiểm tra HTTP thực qua PHP built-in server: đăng nhập, tạo đợt, ngăn trùng lịch, chặn CSRF sai, lưu SQLite, đăng xuất, nhật ký đăng xuất và thử lại cookie phiên cũ: đạt.
- Dữ liệu kiểm tra được tạo trong thư mục tạm, không đưa vào ZIP.
- Chưa kiểm tra giao diện bằng trình duyệt đồ họa hoặc chạy trực tiếp trên XAMPP Windows.
- Chưa thực thi 63 test case thủ công trong file Word. Chưa push GitHub.

Các kiểm thử tự động xác nhận quy tắc giả định của demo; không thay thế việc giảng viên xác nhận nghiệp vụ.

Bản cập nhật cấu trúc: Controller, Core, Model, Service, Views/UC11, database và hướng dẫn TXT riêng. Đã chạy lại toàn bộ kiểm tra nêu trên sau khi tách lớp.
