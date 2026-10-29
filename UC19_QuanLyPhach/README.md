# UC19 — Quản lý phách theo túi chấm sau dồn túi

Triển khai theo tài liệu **UC19_Quan_ly_phach_bai_thi (1).docx** người dùng cung cấp ngày 29/10/2026. PHP 8.1+ / MySQL hoặc MariaDB, cấu trúc MVC tương tự dự án PTUD, mã nguồn nằm riêng tại UC19_QuanLyPhach.

## Chạy trên XAMPP

1. Bật Apache và MySQL.
2. Import [database/uc19_demo.sql](database/uc19_demo.sql) bằng phpMyAdmin. Script tạo CSDL **uc19_demo**; script sẽ báo lỗi nếu CSDL này đã tồn tại và không ghi đè dữ liệu.
3. Mở **http://localhost/PTUD_QLTS10_IS/UC19_QuanLyPhach/**.
4. Đăng nhập **thuky / ThuKy@123**.

Kết nối mặc định: 127.0.0.1:3306, root, mật khẩu rỗng. Cấu hình ở [app/Core/uc19-config.php](app/Core/uc19-config.php); có thể ghi đè bằng app/Core/uc19-config.local.php trả về mảng PHP. Nếu có cấu hình local, đặt db_name thành uc19_demo.

Bản này sử dụng session riêng và CSDL uc19_demo. File SQL chỉ dành cho cài đặt mới và sẽ báo lỗi nếu database đích đã tồn tại. Nếu cần giữ dữ liệu trong database UC19 cũ, hãy sao lưu trước; phiên bản này không kèm công cụ chuyển dữ liệu tự động. Không tự chuyển mã bì theo phòng sang túi chấm; dữ liệu dồn túi phải được xác lập từ biên bản bàn giao.

## Luồng sử dụng theo UC mới

- Chỉ chọn **kỳ thi và môn thi tự luận**. Không chọn điểm thi/phòng thi.
- Danh sách túi hiển thị mã túi, số bài, số tờ giấy thi và trạng thái tạo phách.
- Chọn túi để xem biên bản bàn giao và danh sách họ tên thí sinh, SBD, số tờ; phòng nguồn chỉ hiển thị cho người có quyền đối chiếu.
- Nhấn **Tạo số phách**: kiểm tra túi chưa tạo phách; danh sách đầy đủ, đúng kỳ/môn; số bài và tổng số tờ khớp biên bản; không có bài ở hai túi cùng kỳ/môn.
- Xem trước phách; **Tạo lại** chỉ thay một phách; **Hủy** loại bỏ bản nháp.
- **Xác nhận lưu** ghi quan hệ, trạng thái, người/thời gian và nhật ký trong một transaction.
- Sau khi lưu không cho tạo lại. Có thể chọn túi khác hoặc **Xem dữ liệu phục vụ chấm thi**.

**Bài thi chỉ có số phách làm định danh:** trước khi lưu, hệ thống chỉ lưu dữ liệu bàn giao theo thí sinh/SBD, phòng và số tờ. Bản xem trước nằm trong session; khi xác nhận mới thêm bản ghi vào bảng papers. Không có mã bài thi riêng. Quy tắc demo cố định: 9 chữ số ngẫu nhiên bằng random_int, không dựa trên SBD hoặc thứ tự bài, duy nhất trong toàn kỳ thi/môn thi.

**Số bài khác số tờ:** một bài thi có thể có nhiều tờ. Ví dụ TUI-T01 có 3 bài, lần lượt 2 + 1 + 3 tờ, tổng cộng 6 tờ.

Các dữ liệu sau dồn túi được chuẩn bị trước UC19; ứng dụng này không thực hiện thu bài, mở niêm phong, dồn túi hoặc nhập điểm.

## Cấu trúc MVC

| Thành phần | Trách nhiệm |
| --- | --- |
| [public/uc19.php](public/uc19.php) | Điểm vào, session và tải thành phần |
| [app/Controllers/UC19Controller.php](app/Controllers/UC19Controller.php) | Request, phân quyền, bộ lọc, bản nháp session, chuyển trang |
| [app/Models/UC19Model.php](app/Models/UC19Model.php) | PDO, túi chấm, đối chiếu, lưu giao dịch, nhật ký |
| [app/Services/UC19Service.php](app/Services/UC19Service.php) | Kiểm tra số bài/số tờ, phách ngẫu nhiên, tính duy nhất |
| [app/Views/UC19/](app/Views/UC19/) | Đăng nhập, quản lý túi, dữ liệu chấm thi, lỗi quyền |
| [database/uc19_demo.sql](database/uc19_demo.sql) | Schema và dữ liệu mẫu mới |
| [tests/test_uc19.php](tests/test_uc19.php) | Kiểm thử theo UC mới |

Các màn hình danh sách thí sinh toàn phòng và bàn giao từng mã bì của bản cũ đã được bỏ.

## Dữ liệu và ràng buộc

- exam_subjects xác định một cặp kỳ thi/môn thi.
- grading_bags lưu số bài, số tờ, biên bản bàn giao và trạng thái của từng túi; không gắn túi với một phòng.
- handover_entries lưu các dòng bàn giao theo thí sinh, phạm vi kỳ/môn, túi, phòng và số tờ. id là khóa kỹ thuật của dòng bàn giao, không phải mã bài thi. Dữ liệu trùng thí sinh giữa các túi được giữ để phát hiện lỗi.
- papers chỉ lưu bài đã xác nhận đánh phách. Khóa chính (scope_id,number) là số phách trong kỳ/môn; UNIQUE(scope_id,student_id) ngăn lưu hai bài của cùng thí sinh trong cùng kỳ/môn. handover_id liên kết về dòng bàn giao.
- audit_logs ghi người, thời điểm, hành động, số bài, số tờ trong cùng giao dịch với phách.

Thông tin đối chiếu thiếu được giữ ở dữ liệu đầu vào để báo sai lệch; không được phép lưu phách khi còn sai. Khi xác nhận, hệ thống kiểm tra lại quyền, dữ liệu và bản xem trước. Khóa theo kỳ/môn và transaction SERIALIZABLE trên MySQL bảo vệ dữ liệu lúc lưu. Nếu lỗi bất kỳ bước nào, kể cả ghi nhật ký, toàn bộ giao dịch được rollback.

## Phân quyền và bảo mật

| Tài khoản demo (cùng mật khẩu ThuKy@123) | Quyền |
| --- | --- |
| thuky | Quản lý phách và xem dữ liệu đối chiếu |
| khongquyen | Không có quyền quản lý phách, bị từ chối đăng nhập UC19 |
| khongdoichieu | Có quyền truy cập dữ liệu phách nhưng không được mở bảng đối chiếu, tạo/lưu hoặc xem nhật ký |

Trang dữ liệu chấm chỉ lấy **số phách và số tờ**; đầu trang có kỳ/môn và mã túi, không có mã bài, họ tên, SBD, mã học sinh, phòng hay điểm thi. Quyền xem đối chiếu được kiểm tra cả ở Controller và Model. Đường dẫn trang chấm: public/uc19.php?exam=1&subject=1&bag=1&view=grading.

Dùng CSRF cho POST, kiểm tra token bản nháp để chặn xác nhận nhầm giữa các tab, password hash, prepared statements và escape HTML. Khi triển khai thật, thay tài khoản demo, dùng tài khoản DB riêng và đặt DocumentRoot tại public/.

## Dữ liệu mẫu để thử các nhánh

| Phạm vi / túi | Dữ liệu |
| --- | --- |
| 2026 / Toán / TUI-T01 | Hợp lệ: 3 bài từ P.101, P.102, P.201; tổng 6 tờ |
| 2026 / Toán / TUI-T02 | Hợp lệ: 2 bài từ hai phòng; tổng 3 tờ |
| TUI-T03 | Túi rỗng |
| TUI-T04 | Biên bản 3 bài, danh sách 2 bài |
| TUI-T05 | Biên bản 7 tờ, danh sách tổng 4 tờ |
| TUI-T06 | Thiếu biên bản bàn giao |
| TUI-T07 và TUI-T08 | Cùng chứa dữ liệu bàn giao của thí sinh SBD 260011, bị từ chối do trùng túi |
| TUI-T09 | Thiếu số tờ của bài |
| TUI-T10 | Thiếu thông tin thí sinh đối chiếu |
| 2026 / Ngữ văn / TUI-V01 | Hợp lệ, khác phạm vi môn thi |
| 2027 / Toán | Không có túi, hiển thị thông báo ngoại lệ 4.1 |

## Kiểm thử

Chạy trong PowerShell tại thư mục gốc:

```powershell
& C:\xampp\php\php.exe tests/test_uc19.php
```

20 kiểm thử bằng SQLite trong bộ nhớ, dùng chính schema/seed (đổi cú pháp MySQL cho SQLite). TC01–TC10 tương ứng 10 test case trong tài liệu; các kiểm thử bổ sung bao phủ phân quyền đối chiếu, đầu ra chấm thi không lộ nguồn, dữ liệu thay đổi, xung đột phách lúc lưu, ràng buộc DB và thu hồi quyền.

Không sửa CSDL demo khi chạy các kiểm thử này. SQLite không mô phỏng khóa MySQL; việc chạy trên XAMPP và kiểm tra nhiều phiên đồng thời là phần xác minh riêng. Lỗi lưu giữa giao dịch được mô phỏng bằng trigger trong CSDL kiểm thử, không có nút gây lỗi trên giao diện ứng dụng.
