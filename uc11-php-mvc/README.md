# UC11 Thiết lập và quản lý đợt lịch tuyển sinh

Demo **PHP MVC + Service + SQLite**, cấu trúc đặt tên theo UC11. Chạy với PHP 8.1 trở lên và PDO SQLite, không cần Composer hoặc MySQL.

**Hướng dẫn chi tiết:** mở tệp [HUONG_DAN_UC11_MVC.txt](HUONG_DAN_UC11_MVC.txt).

## Chạy nhanh trên XAMPP

1. Giải nén vào `C:\xampp\htdocs\uc11-php-mvc`.
2. Bật `extension=pdo_sqlite` trong PHP php.ini và khởi động lại Apache.
3. Mở **http://localhost/uc11-php-mvc/public/uc11.php**.
4. Đăng nhập **canbo / Demo@123**.

CSDL tự tạo tại `storage/admissions.sqlite`. Không cần import SQL hoặc mở MySQL.

Nếu máy có PHP, chạy từ thư mục dự án:

```bash
php -S 127.0.0.1:8000 -t public
```

Mở http://127.0.0.1:8000/uc11.php.

## Cấu trúc

```text
app/
  Controllers/
    UC11Controller.php
  Core/
    Database.php
    uc11-config.php
    uc11-helpers.php
  Models/
    UC11Model.php
  Services/
    UC11Service.php
  Views/
    UC11/
      error.php
      index.php
      layout.php
      login.php
database/
  uc11_demo.sql
public/
  assets/
    style.css
  index.php
  uc11.php
storage/
  .gitkeep
tests/
  test_uc11_algorithm.php
.gitignore
.htaccess
HUONG_DAN_UC11_MVC.txt
KET_QUA_KIEM_TRA.md
README.md
```

| Thành phần | Nhiệm vụ |
|---|---|
| Controller | Nhận yêu cầu, kiểm tra phiên/CSRF, điều phối Service và View |
| Core | Kết nối CSDL, cấu hình và hàm hỗ trợ |
| Model | Truy vấn SQL, giao dịch, đọc/ghi đợt và nhật ký |
| Service | Quy tắc thời gian, trùng lịch, điều kiện mở/đóng, phiên bản dữ liệu |
| View | Hiển thị biểu mẫu, danh sách, nhật ký và thông báo |
| database | Lược đồ SQLite được ứng dụng tự thực thi |
| public | Điểm vào và tài nguyên giao diện |
| tests | Kiểm tra nghiệp vụ bằng CSDL trong bộ nhớ |

Service không chứa SQL; View không truy cập CSDL. Tạo/cập nhật đợt và ghi nhật ký nằm trong cùng giao dịch. Ứng dụng chuyển về GET sau POST để tránh gửi lại thao tác khi tải trang.

## Chức năng

Tạo đợt, điều chỉnh lịch đợt chưa mở, kiểm tra dữ liệu và xung đột, mở/đóng thủ công, lưu nhật ký, đăng nhập/đăng xuất hỗ trợ. Không có xóa đợt vì đặc tả UC11 không mô tả.

**Giả định cần xác nhận:** tên bắt buộc tối đa 150 ký tự; bốn mốc tăng nghiêm ngặt; khoảng từ mở đăng ký đến xác nhận nhập học không giao nhau kể cả biên. Chỉ mở đợt chưa mở trong khoảng đăng ký; chỉ đóng đợt đang mở khi đến hạn đóng đăng ký. Chỉ sửa đợt chưa mở; không mở lại hoặc tự động chuyển trạng thái. Thời gian Việt Nam.

Nguồn đặc tả, đọc ngày 28/10/2026:
https://docs.google.com/document/d/1KA1Z2BstZcaavsIaf_-uIQgCyCnfJQsWDELGvauZ5P0/edit?tab=t.0

## Kiểm thử

```bash
php tests/test_uc11_algorithm.php
```

Đã đạt 18 kiểm tra nghiệp vụ, kiểm tra cú pháp 13 tệp PHP và kiểm tra HTTP cho đăng nhập, tạo đợt, trùng lịch, CSRF, dữ liệu lưu, đăng xuất và phiên cũ. Xem KET_QUA_KIEM_TRA.md. Chưa chạy trực tiếp trên XAMPP Windows hoặc kiểm tra giao diện bằng trình duyệt đồ họa.

Bộ test case Word gửi riêng có 63 ca UC10, UC11, UC12 và UC24; các ca thủ công chưa thực thi. Mã chỉ triển khai UC11 và đăng nhập/đăng xuất hỗ trợ.

## GitHub và phạm vi

Các lệnh tạo repo và push có trong tệp hướng dẫn. CSDL được loại khỏi Git. Chưa tạo hoặc push repo thay bạn.

Demo phục vụ học tập với tài khoản cố định. Khi triển khai máy chủ, đặt DocumentRoot vào public. Tệp .htaccess ở gốc hỗ trợ chặn thư mục nội bộ trên Apache có mod_rewrite và AllowOverride phù hợp, không thay thế cấu hình máy chủ.
