# UC16 – Thực hiện xét tuyển

Ứng dụng PHP theo cấu trúc MVC mô phỏng đầy đủ use case **“Thực hiện xét tuyển”** cho cán bộ Phòng Quản lý tuyển sinh – Sở GD&ĐT.

## Chức năng

- Hiển thị danh sách đợt xét tuyển và chỉ tiêu từng trường THPT.
- Kiểm tra dữ liệu đầu vào trước khi xét tuyển.
- Xét lần lượt NV1 → NV2 → NV3 theo điểm từ cao xuống thấp.
- Mỗi học sinh chỉ trúng tuyển nguyện vọng ưu tiên cao nhất đạt được.
- Không tuyển vượt chỉ tiêu; chấp nhận trường còn thiếu chỉ tiêu.
- Tổng hợp số trúng tuyển NV1, NV2, NV3 và điểm thấp nhất từng trường.
- Xem danh sách chi tiết của từng trường.
- Kiểm tra kết quả rồi xác nhận lưu chính thức.
- Có xử lý lỗi thiếu dữ liệu, lỗi lưu, CSRF và giao dịch cơ sở dữ liệu.

## Công nghệ

- PHP 8.1 trở lên, không dùng framework.
- PDO + SQLite; cơ sở dữ liệu và dữ liệu mẫu được tạo tự động lần đầu.
- HTML/CSS responsive.
- Không cần Composer để chạy. `composer.json` chỉ hỗ trợ IDE/autoload chuẩn PSR-4.

## Cấu trúc MVC

```text
app/
├── Controllers/       Điều phối request
├── Core/              Database, View, CSRF, Flash, autoload
├── Models/            Truy vấn dữ liệu
├── Services/          Thuật toán xét tuyển và transaction
└── Views/             Giao diện
config/                Cấu hình ứng dụng
database/              Schema và dữ liệu mẫu
public/                Điểm vào website và CSS
storage/               File SQLite phát sinh khi chạy
tests/                 Kiểm thử thuật toán
```

## Chạy bằng XAMPP trên Windows

1. Chép thư mục `uc16-php-mvc` vào `C:\xampp\htdocs`.
2. Mở XAMPP và bật **Apache**.
3. Truy cập `http://localhost/uc16-php-mvc/`.

Không cần bật MySQL vì project dùng SQLite. Lần đầu truy cập, file `storage/admission.sqlite` và dữ liệu mẫu được tạo tự động.

Nếu máy báo thiếu SQLite, mở `C:\xampp\php\php.ini`, kiểm tra hai dòng sau không có dấu `;` ở đầu rồi khởi động lại Apache:

```ini
extension=pdo_sqlite
extension=sqlite3
```

Có thể chạy kiểm thử trong XAMPP Shell:

```bash
php tests/AdmissionServiceTest.php
```

## Đưa source lên GitHub

Tạo repository trống trên GitHub, sau đó mở Terminal tại thư mục project:

```bash
git init
git add .
git commit -m "Hoan thanh UC16 thuc hien xet tuyen"
git branch -M main
git remote add origin https://github.com/TEN_TAI_KHOAN/TEN_REPOSITORY.git
git push -u origin main
```

Workflow `.github/workflows/php-ci.yml` sẽ tự kiểm tra cú pháp và thuật toán sau mỗi lần push.

> GitHub chỉ dùng để lưu và nộp source. GitHub Pages không chạy PHP; project này được thiết kế để chạy bằng XAMPP.

## Quy tắc xét tuyển

Ở mỗi nguyện vọng, thí sinh được nhóm theo trường và xếp theo:

1. Tổng điểm giảm dần.
2. Số báo danh tăng dần nếu bằng điểm (quy tắc ổn định để kết quả có thể lặp lại).

Sau khi đỗ một nguyện vọng, thí sinh được loại khỏi các nguyện vọng sau. Kết quả ban đầu ở trạng thái `draft`; chỉ khi cán bộ chọn **Xác nhận kết quả** mới chuyển thành `confirmed`.

## Dữ liệu mẫu

Dữ liệu mẫu gồm 3 trường, 12 thí sinh và đủ kết quả NV1, NV2, NV3. Muốn tạo lại dữ liệu từ đầu, xóa file `storage/admission.sqlite` rồi tải lại trang.

