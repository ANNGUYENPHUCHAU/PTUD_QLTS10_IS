# UC14 — Cấp số báo danh và phân phòng thi cho thí sinh

Bản demo PHP 8.2+ / MySQL (MariaDB) cho môn **Phát triển ứng dụng**. Cơ sở nghiệp vụ là tài liệu UC14 do nhóm cung cấp: cán bộ Sở GD&ĐT thực hiện cấp SBD và phân phòng từ kết quả phân điểm thi, có xem trước, đổi phòng, xác nhận và lưu. Đây là **bản demo độc lập**, không phải module đã tích hợp vào hệ thống chính.

## 1. Quy tắc TỰ BỔ SUNG để hoàn thiện chỗ còn thiếu trong đặc tả

Các quy tắc sau **không được nêu cụ thể trong tài liệu gốc**, là giả định kỹ thuật để làm demo; có thể sửa theo phản hồi giảng viên:

1. SBD là chuỗi **6 chữ số**, bắt đầu `000001`, tăng liên tục **toàn bộ các điểm thi**, không lặp trong CSDL. Sau khi gán thì giữ nguyên SBD khi đổi phòng. Khi sang kỳ tuyển sinh mới, cần thiết kế bổ sung khóa kỳ thi và quy tắc đánh số tương ứng.
2. Điểm thi xử lý theo `ma_diem_thi` tăng dần; trong mỗi điểm, thí sinh sắp theo **tên gọi** (từ cuối họ tên) A–Z, tiếp theo họ tên đầy đủ, rồi mã học sinh để ổn định thứ tự. Bỏ dấu tiếng Việt khi so sánh.
3. Phòng thi xử lý theo `so_phong` tăng tự nhiên (Phòng 2 trước Phòng 10); lần lượt xếp đủ phòng thứ nhất rồi chuyển sang phòng tiếp theo. Sức chứa dùng từ bảng `phong_thi.suc_chua`.
4. Nếu **bất kỳ điểm thi nào** thiếu chỗ, không tạo bản xem trước và không lưu kết quả của điểm nào (toàn bộ hoặc không).
5. Đổi phòng chỉ được thực hiện **trước khi xác nhận**, chỉ trong **cùng điểm thi**, phòng đích không vượt sức chứa.
6. Sau khi xác nhận, chức năng **không tự ghi đè** kết quả cũ. Việc lưu chạy trong **một giao dịch MySQL**, lỗi thì rollback toàn bộ. Có kiểm tra thay đổi dữ liệu đầu vào giữa xem trước và xác nhận.
7. `Đặt lại dữ liệu demo` là **nút chỉ phục vụ thử nghiệm** (không có trong UC14): xóa kết quả phân phòng để có thể chạy lại, không dùng cho hệ thống thật.
8. Đăng nhập `canbo / Demo@123` cũng chỉ là **tài khoản demo**, không thay thế phân quyền người dùng thật.

## 2. Nội dung bộ code

```
UC14_CapSBD_PhanPhongThi/
├── app/
│   ├── config.php              # Cấu hình mặc định localhost
│   ├── Database.php            # Kết nối PDO
│   └── UC14Service.php         # Quy tắc cấp SBD, phân phòng, lưu/rollback
├── public/
│   ├── index.php               # Đăng nhập, giao diện và hành động
│   └── assets/style.css        # CSS offline
├── database/
│   └── uc14_demo.sql           # CSDL và 45 thí sinh GIẢ LẬP
├── tests/
│   └── test_algorithm.php      # Kiểm tra thuật toán không cần CSDL
├── .gitignore
└── README.md
```

## 3. Cài và chạy trên Windows + XAMPP

1. Giải nén, chép thư mục **UC14_CapSBD_PhanPhongThi** vào `D:\xampp\htdocs\`. Nếu bạn cài XAMPP ở ổ C thì dùng `C:\xampp\htdocs\`.
2. Mở `xampp-control.exe`, nhấn **Start** tại **Apache** và **MySQL**; hai hàng chuyển màu xanh.
3. Truy cập **http://localhost/phpmyadmin** trong Chrome. Chọn tab **Import** (Nhập), **Choose File** → chọn file `D:\xampp\htdocs\UC14_CapSBD_PhanPhongThi\database\uc14_demo.sql` → **Import/Go**. SQL tự tạo database `uc14_demo`, các bảng và 45 thí sinh giả lập.
4. Truy cập **http://localhost/UC14_CapSBD_PhanPhongThi/public/**.
5. Đăng nhập bằng **canbo / Demo@123** (chỉ là tài khoản mô phỏng).
6. Bấm **Cấp SBD & phân phòng tự động** → xem 45 dòng → có thể **Đổi** phòng một học sinh trong cùng điểm thi → bấm **Xác nhận & lưu kết quả**. Mở phpMyAdmin, chọn `uc14_demo` và bảng `ket_qua_phan_phong_thi` để xem dữ liệu đã lưu.
7. Muốn demo lại, dùng nút **Đặt lại dữ liệu demo**. Nút này xóa các kết quả đã lưu; chỉ dùng cho dữ liệu giả lập.

### Nếu không chạy được

- **Không vào localhost:** kiểm tra Apache chạy, nếu cổng là 8080 thì thử `http://localhost:8080/...`.
- **Không vào phpMyAdmin:** kiểm tra MySQL trong XAMPP.
- **Báo không thể kết nối MySQL:** kiểm tra đã Import SQL và MySQL có thể chạy ở port **3307** thay vì 3306. Tạo file `app/config.local.php` với:

  ```php
  <?php
  return ['db_port' => '3307'];
  ```

  Không đẩy `config.local.php` lên GitHub.
- **Bị lỗi sau khi bấm xác nhận:** kiểm tra bảng trong phpMyAdmin và log Apache; hệ thống cố gắng hoàn tác toàn bộ giao dịch khi có lỗi.

## 4. Kiểm thử nhanh (theo Use Case)

| Mã | Thao tác | Kết quả mong đợi |
|---|---|---|
| TC01 | Đăng nhập `canbo / Demo@123` | Vào trang chính |
| TC02 | Cấp SBD & phân phòng tự động | 45 thí sinh, SBD không trùng, sắp tên A–Z, không quá sức chứa |
| TC03 | Đổi HS ở Phòng 01 → Phòng 02 của DT01 còn chỗ | Đổi thành công, giữ nguyên SBD |
| TC04 | Cố chuyển một HS từ Phòng 02 → Phòng 01 đã đủ 20/20 | Báo phòng đủ sức chứa, giữ nguyên bản tạm |
| TC05 | Xác nhận & lưu | Có 45 dòng ở `ket_qua_phan_phong_thi`, thông báo thành công |
| TC06 | Tạo lại sau khi đã lưu | Không ghi đè dữ liệu đã xác nhận |
| TC07 | Đặt lại demo; trong phpMyAdmin sửa `phong_thi.P0102.suc_chua=5`, chạy lại | DT01 cần 30 chỗ nhưng chỉ có 25; báo thiếu chỗ và không lưu |
| TC08 | Đặt lại `P0102.suc_chua=20` rồi chạy lại | Có thể cấp SBD, phân phòng bình thường |
| TC09 | Khi chưa xác nhận, sửa sức chứa một phòng trong database rồi xác nhận | Báo dữ liệu thay đổi, yêu cầu tạo lại |
| TC10 | Khi lưu DB lỗi (ví dụ ngắt MySQL) | Báo không lưu được, không được coi là thành công |

> Lưu ý: TC07/TC09 dùng thay đổi dữ liệu kiểm thử trong phpMyAdmin; nhớ trả lại sức chứa **20** để demo thành công.

Nếu có PHP CLI, chạy `php tests/test_algorithm.php` để kiểm tra thuật toán không cần MySQL.

## 5. Đưa lên GitHub nhóm (sau khi chạy và kiểm thử)

Repository nhóm: `https://github.com/ANNGUYENPHUCHAU/PTUD_QLTS10_IS`.

- Mỗi thành viên **clone** repository về máy và tạo **branch riêng**, ví dụ `feature/uc14-phan-phong`.
- Chép thư mục dự án này vào trong repo đã clone, không đẩy lên `main` trực tiếp.
- Trong Git Bash ở thư mục repo, lần lượt dùng `git add UC14_CapSBD_PhanPhongThi`, `git commit -m "Add UC14 demo"`, `git push -u origin feature/uc14-phan-phong`.
- Lên GitHub mở **Pull Request**, nhờ trưởng nhóm kiểm tra rồi merge vào `main`.
- Lưu ý các thành viên phải thống nhất đường dẫn và cấu trúc cuối cùng. Khi tích hợp 5 UC vào một hệ thống, cần thống nhất kết nối CSDL, tài khoản và menu chung; không sao chép nguyên phần đăng nhập demo này vào hệ thống thật.

## 6. Những phần chưa phải hệ thống thật

- Chưa có một quy tắc SBD chính thức đã được cô xác nhận; đang dùng giả định 6 chữ số trên toàn bộ các điểm thi.
- Dữ liệu 45 học sinh là mô phỏng, không lấy từ hệ thống quản lý tuyển sinh thật.
- Chưa kết nối với Use Case *phân điểm thi* thực tế của các thành viên khác; demo có bảng `ket_qua_phan_diem_thi` giả lập làm nguồn.
- Chưa có phân quyền cán bộ thực, kỳ tuyển sinh nhiều năm, lịch sử chỉnh sửa và audit log.
- Không công khai tài khoản, thông tin học sinh thực hoặc mật khẩu trong repository.
