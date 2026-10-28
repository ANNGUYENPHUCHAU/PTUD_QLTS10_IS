# HƯỚNG DẪN CHẠY CHỨC NĂNG UC14 – MVC

**Chức năng:** UC14 – Cấp số báo danh và phân phòng thi cho thí sinh.

**Dự án:** Hệ thống quản lý tuyển sinh lớp 10.

## 1. Cấu trúc dự án

Chức năng UC14 được tổ chức theo mô hình MVC độc lập, nằm trong thư mục `UC14_CapSBD_PhanPhongThi/`.

- `app/Controllers/UC14Controller.php`: Tiếp nhận và xử lý yêu cầu từ người dùng.
- `app/Models/UC14Model.php`: Truy xuất và cập nhật dữ liệu MySQL.
- `app/Views/UC14/`: Chứa giao diện đăng nhập, giao diện chính và trang thông báo lỗi.
- `app/Services/UC14Service.php`: Xử lý thuật toán cấp số báo danh, phân phòng thi và kiểm tra các ràng buộc.
- `app/Core/`: Chứa kết nối cơ sở dữ liệu, cấu hình và các hàm hỗ trợ.
- `public/index.php`: Điểm truy cập chính của chức năng UC14.
- `public/uc14.php`: Điểm truy cập bổ sung, phục vụ điều hướng trong ứng dụng.
- `public/assets/uc14.css`: Định dạng giao diện.
- `database/uc14_demo.sql`: Cấu trúc và dữ liệu MySQL mẫu.
- `tests/test_uc14_algorithm.php`: Kiểm thử thuật toán.

## 2. Hướng dẫn cài đặt và chạy

**Bước 1:** Đặt thư mục dự án tại:

`D:\xampp\htdocs\PTUD_QLTS10_IS`

**Bước 2:** Mở XAMPP Control Panel, khởi động Apache và MySQL.

**Bước 3:** Mở phpMyAdmin và kiểm tra cơ sở dữ liệu `uc14_demo`.

Nếu cài đặt trên máy mới, tạo cơ sở dữ liệu và nhập file:

`UC14_CapSBD_PhanPhongThi/database/uc14_demo.sql`

Nếu cơ sở dữ liệu đã tồn tại và chứa kết quả cần giữ, không nhập lại dữ liệu mẫu.

**Bước 4:** Mở trình duyệt và truy cập:

http://localhost/PTUD_QLTS10_IS/UC14_CapSBD_PhanPhongThi/public/index.php

Đăng nhập bằng tài khoản demo được cung cấp trong giao diện.

## 3. Các chức năng chính

- Hiển thị danh sách điểm thi và thông tin thí sinh.
- Tự động cấp số báo danh theo thứ tự tên A–Z.
- Tự động phân phòng thi theo sức chứa.
- Xem trước kết quả cấp số báo danh và phân phòng.
- Điều chỉnh phòng thi trước khi xác nhận, bảo đảm các ràng buộc.
- Xác nhận và lưu kết quả vào MySQL.
- Tra cứu kết quả cấp số báo danh và phân phòng đã lưu.

## 4. Kiểm thử

Mở Terminal tại thư mục:

`D:\xampp\htdocs\PTUD_QLTS10_IS`

Chạy lệnh:

`D:\xampp\php\php.exe UC14_CapSBD_PhanPhongThi\tests\test_uc14_algorithm.php`

**Kết quả kiểm thử đã thực hiện:** 11/11 bài kiểm thử thuật toán PASS.

Các trường hợp kiểm thử bao gồm cấp số báo danh, thứ tự sắp xếp, phân phòng, sức chứa, điều chỉnh phòng và kiểm tra các ràng buộc.

## 5. Lưu ý

- UC14 được tổ chức thành module MVC độc lập, tương tự cách bố trí thư mục của UC16.
- UC14 hiện sử dụng cơ sở dữ liệu MySQL `uc14_demo` riêng.
- Không tự ý xóa hoặc đặt lại dữ liệu đã lưu khi trình diễn.
- Các file cấu hình cá nhân chứa thông tin nhạy cảm không được đưa lên GitHub.
- UC14 hiện hoạt động độc lập, chưa phải là chức năng đã tích hợp hoàn chỉnh với toàn bộ hệ thống tuyển sinh.
- Source code được quản lý trong repository GitHub chung của nhóm.

**Repository:** https://github.com/ANNGUYENPHUCHAU/PTUD_QLTS10_IS
