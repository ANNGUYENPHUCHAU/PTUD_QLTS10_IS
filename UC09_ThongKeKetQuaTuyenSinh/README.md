# HƯỚNG DẪN CHẠY CHỨC NĂNG UC09 – MVC

**Chức năng:** UC09 – Thống kê kết quả tuyển sinh.

**Dự án:** Hệ thống Quản lý Tuyển sinh Lớp 10.

**Người thực hiện:** Thái Bá Cường.

---

## 1. Cấu trúc module độc lập

Chức năng UC09 được tổ chức theo mô hình MVC độc lập, tương tự cách bố trí thư mục của `UC14_CapSBD_PhanPhongThi`, `UC16_ThucHienXetTuyen` và `UC19_QuanLyPhach`, nằm trọn trong thư mục `UC09_ThongKeKetQuaTuyenSinh/`.

```
UC09_ThongKeKetQuaTuyenSinh/
├── .gitignore
├── index.php                        # Điều hướng vào public/uc09.php
├── README.md                        # Hướng dẫn chi tiết
├── app/
│   ├── Controllers/
│   │   └── UC09Controller.php       # Tiếp nhận và xử lý yêu cầu, xuất CSV
│   ├── Core/
│   │   ├── Database.php             # Kết nối cơ sở dữ liệu PDO
│   │   ├── uc09-config.php          # Cấu hình CSDL (hỗ trợ file local)
│   │   └── uc09-helpers.php         # Các hàm tiện ích (h, csrf, flash)
│   ├── Models/
│   │   └── UC09Model.php            # Xử lý truy vấn và tính toán thống kê
│   └── Views/
│       ├── Partials/
│       │   └── sidebar.php          # Thanh điều hướng phân hệ
│       └── UC09/
│           └── index.php            # Giao diện dashboard thống kê
├── database/
│   └── uc09_demo.sql                # Cấu trúc bảng và dữ liệu mẫu đầy đủ
├── public/
│   ├── assets/
│   │   └── uc09.css                 # CSS giao diện độc lập
│   ├── index.php                    # Điểm truy cập chính
│   └── uc09.php                     # Điểm truy cập nghiệp vụ UC09
└── tests/
    └── test_uc09.php                # Kịch bản kiểm thử tự động
```

---

## 2. Hướng dẫn cài đặt và chạy

### Cách 1: Chạy trên XAMPP
1. Đặt toàn bộ thư mục dự án tại `D:\xampp\htdocs\PTUD_QLTS10_IS` (hoặc thư mục htdocs của bạn).
2. Khởi động Apache và MySQL trên XAMPP Control Panel.
3. Mở phpMyAdmin và nhập (import) file cơ sở dữ liệu:
   `UC09_ThongKeKetQuaTuyenSinh/database/uc09_demo.sql`
4. Mở trình duyệt và truy cập:
   `http://localhost/PTUD_QLTS10_IS/UC09_ThongKeKetQuaTuyenSinh/`
   hoặc:
   `http://localhost/PTUD_QLTS10_IS/UC09_ThongKeKetQuaTuyenSinh/public/uc09.php`

### Cách 2: Chạy bằng PHP Built-in Server
1. Mở Terminal tại thư mục gốc của dự án.
2. Chạy lệnh:
   ```bash
   php -S 127.0.0.1:8000
   ```
3. Mở trình duyệt và truy cập:
   `http://127.0.0.1:8000/UC09_ThongKeKetQuaTuyenSinh/public/uc09.php`

---

## 3. Các chức năng chính của UC09
- **Thống kê chỉ tiêu:** Tổng chỉ tiêu tuyển sinh, chỉ tiêu còn lại theo từng trường THPT và từng năm.
- **Tính toán tỷ lệ chọi:** Tự động tính tỷ lệ số hồ sơ đăng ký trên tổng chỉ tiêu tuyển sinh của trường.
- **Phân tích theo nguyện vọng:** Tổng hợp số lượng hồ sơ nộp và số trúng tuyển theo NV1, NV2, NV3.
- **Thống kê xác nhận nhập học:** Tỷ lệ thí sinh đã xác nhận nhập học trực tuyến so với số trúng tuyển.
- **Tra cứu và lọc hồ sơ:** Lọc danh sách thí sinh theo các tiêu chí (Tất cả, Đủ điều kiện, Trúng tuyển, Đã nhập học, Không trúng tuyển).
- **Xuất báo cáo:** Hỗ trợ xuất dữ liệu danh sách thí sinh ra file CSV (chuẩn UTF-8 mở được trên Excel).

---

## 4. Kiểm thử tự động

Chạy kịch bản kiểm thử tự động từ Terminal:
```bash
php UC09_ThongKeKetQuaTuyenSinh/tests/test_uc09.php
```

Kịch bản kiểm tra toàn diện các hàm nghiệp vụ trong Model:
- Đọc danh sách trường THPT.
- Đọc năm tuyển sinh.
- Đọc chỉ tiêu và điểm chuẩn.
- Tính toán tỷ lệ chọi và tỷ lệ nhập học.
- Lọc danh sách thí sinh trúng tuyển và đã xác nhận nhập học.

---

## 5. Lưu ý
- UC09 được xây dựng hoàn toàn độc lập, không phụ thuộc vào code của các Use Case khác.
- Kết nối CSDL mặc định tới `127.0.0.1:3306`, database `uc14_demo`, tài khoản `root` mật khẩu rỗng. Nếu máy bạn dùng thông số khác, tạo file `app/Core/uc09-config.local.php` để ghi đè mà không ảnh hưởng tới Git.
