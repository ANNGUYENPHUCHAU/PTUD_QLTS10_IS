
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hệ thống quản lý tuyển sinh lớp 10</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f3f6fc;
    color: #172b4d;
}
header {
    background: #102344;
    color: white;
    padding: 24px 40px;
}
header h1 { margin: 0 0 8px; }
header p { margin: 0; color: #c7d7f0; }
main {
    max-width: 1150px;
    margin: 35px auto;
    padding: 0 20px;
}
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 20px;
}
.card {
    background: white;
    border: 1px solid #e1e7f0;
    border-radius: 14px;
    padding: 26px;
    text-decoration: none;
    color: #172b4d;
    transition: .2s;
}
.card:hover {
    border-color: #2563eb;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px #172b4d15;
}
.card h2 { margin: 12px 0; font-size: 21px; }
.card p { color: #64748b; line-height: 1.6; }
.code {
    color: #2563eb;
    font-weight: bold;
}
.action {
    display: inline-block;
    background: #2563eb;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    margin-top: 15px;
}
footer {
    margin: 35px auto;
    text-align: center;
    color: #64748b;
}
</style>
</head>
<body>

<header>
    <h1>HỆ THỐNG QUẢN LÝ TUYỂN SINH LỚP 10</h1>
    <p>Phòng Quản lý tuyển sinh | Dự án Phát triển ứng dụng</p>
</header>

<main>
    <h2>Trang tổng quan</h2>
    <p>Chọn chức năng nghiệp vụ cần thực hiện.</p>

    <div class="grid">
        <a class="card" href="UC09_ThongKeKetQuaTuyenSinh/">
            <span class="code">UC09</span>
            <h2>Thống kê kết quả tuyển sinh</h2>
            <p>Thống kê hồ sơ, chỉ tiêu và kết quả tuyển sinh.</p>
            <span class="action">Truy cập chức năng</span>
        </a>

        <a class="card" href="uc11-php-mvc/public/uc11.php">
            <span class="code">UC11</span>
            <h2>Quản lý đợt và lịch tuyển sinh</h2>
            <p>Thiết lập, điều chỉnh và quản lý lịch tuyển sinh.</p>
            <span class="action">Truy cập chức năng</span>
        </a>

        <a class="card" href="UC14_CapSBD_PhanPhongThi/public/index.php">
            <span class="code">UC14</span>
            <h2>Cấp số báo danh và phân phòng thi</h2>
            <p>Tự động cấp SBD và phân phòng theo sức chứa.</p>
            <span class="action">Truy cập chức năng</span>
        </a>

        <a class="card" href="UC16_ThucHienXetTuyen/">
            <span class="code">UC16</span>
            <h2>Thực hiện xét tuyển</h2>
            <p>Thực hiện xử lý và xác nhận kết quả xét tuyển.</p>
            <span class="action">Truy cập chức năng</span>
        </a>

        <a class="card" href="UC19_QuanLyPhach/">
            <span class="code">UC19</span>
            <h2>Quản lý phách bài thi</h2>
            <p>Quản lý túi chấm thi và tạo số phách.</p>
            <span class="action">Truy cập chức năng</span>
        </a>
    </div>
</main>

<footer>
    Hệ thống quản lý tuyển sinh lớp 10 - Nhóm IS
</footer>

</body>
</html>
