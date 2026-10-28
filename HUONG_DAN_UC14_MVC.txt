# HƯỚNG DẪN CHẠY CHỨC NĂNG UC14 - MVC

Chức năng: Cấp số báo danh và phân phòng thi.

**1. Cấu trúc dự án**

- app/Controllers: Xử lý yêu cầu.
- app/Models: Truy xuất cơ sở dữ liệu.
- app/Views/UC14: Hiển thị giao diện.
- app/Services: Xử lý nghiệp vụ.
- app/Core: Kết nối và cấu hình MySQL.
- public/uc14.php: File chạy chức năng.
- database/uc14_demo.sql: Cơ sở dữ liệu mẫu.
- tests/test_uc14_algorithm.php: Kiểm thử thuật toán.

**2. Cách chạy**

Mở XAMPP, bật Apache và MySQL.

Đường dẫn giao diện:

http://localhost/PTUD_QLTS10_IS/public/uc14.php

**3. Cơ sở dữ liệu**

Tên database: uc14_demo

File SQL mẫu: database/uc14_demo.sql

Chỉ Import SQL khi thiết lập môi trường mới. Không Import lại hoặc đặt lại dữ liệu nếu đang sử dụng database có kết quả cần giữ.

**4. Kiểm thử**

Chạy trong Terminal:

D:\xampp\php\php.exe tests\test_uc14_algorithm.php

Kết quả đã kiểm thử: 11/11 PASS.

**5. Ghi chú**

- Chức năng UC14 đã chuyển sang mô hình MVC.
- Sử dụng source code trong app/, public/ và tests/.
- Không cần thư mục UC14_CapSBD_PhanPhongThi cũ.
- Code được quản lý trên GitHub chung của nhóm.