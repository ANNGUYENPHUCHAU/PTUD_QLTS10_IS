<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UC09: Thống kê kết quả tuyển sinh | QLTS Lớp 10</title>
    <link rel="stylesheet" href="assets/uc14.css">
</head>
<body>
<div class="app-shell">
    <?php renderSidebar('uc09'); ?>

    <main class="main-area">
        <header class="topbar">
            <div>
                <span class="crumb">Tuyển sinh</span>
                <span class="slash">/</span>
                <b>UC09: Thống kê kết quả tuyển sinh</b>
            </div>
            <div>
                <span class="pill pill-blue">Ban tuyển sinh THPT</span>
            </div>
        </header>

        <div class="content">
            <div class="headline">
                <div>
                    <div class="eyebrow">BÁO CÁO & THỐNG KÊ TOÀN DIỆN</div>
                    <h1>Thống kê kết quả tuyển sinh</h1>
                    <p class="muted">Theo dõi chỉ tiêu, tỷ lệ chọi, điểm chuẩn và tình hình xác nhận nhập học của trường THPT.</p>
                </div>
            </div>

            <!-- Bộ lọc trường, năm và tiêu chí -->
            <div class="panel" style="margin-bottom: 24px;">
                <div class="panel-head" style="background: #fafcff;">
                    <div>
                        <h2>Bộ tiêu chí thống kê</h2>
                        <p class="muted">Chọn trường THPT và năm tuyển sinh cần tổng hợp dữ liệu</p>
                    </div>
                </div>
                <div style="padding: 20px 25px;">
                    <form method="get" action="uc09.php" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
                        <div>
                            <label style="display:block; font-size:11px; font-weight:700; color:var(--muted); margin-bottom:6px;">TRƯỜNG THPT</label>
                            <select name="school" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; font-size:13px; min-width:240px; background:white;">
                                <?php foreach ($schools as $s): ?>
                                    <option value="<?= h($s['ma_truong_thpt']) ?>" <?= $s['ma_truong_thpt'] === $selectedSchool ? 'selected' : '' ?>>
                                        <?= h($s['ten_truong']) ?> (<?= h($s['ma_truong_thpt']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label style="display:block; font-size:11px; font-weight:700; color:var(--muted); margin-bottom:6px;">NĂM TUYỂN SINH</label>
                            <select name="year" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; font-size:13px; min-width:130px; background:white;">
                                <?php foreach ($years as $y): ?>
                                    <option value="<?= (int)$y ?>" <?= (int)$y === $selectedYear ? 'selected' : '' ?>>
                                        Năm <?= (int)$y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label style="display:block; font-size:11px; font-weight:700; color:var(--muted); margin-bottom:6px;">LỌC HỒ SƠ THEO TIÊU CHÍ</label>
                            <select name="criterion" style="border:1px solid #dce3ee; padding:9px 12px; border-radius:8px; font-size:13px; min-width:200px; background:white;">
                                <option value="all" <?= $selectedCriterion === 'all' ? 'selected' : '' ?>>Tất cả hồ sơ đăng ký</option>
                                <option value="du_dieu_kien" <?= $selectedCriterion === 'du_dieu_kien' ? 'selected' : '' ?>>Thí sinh đủ điều kiện</option>
                                <option value="trung_tuyen" <?= $selectedCriterion === 'trung_tuyen' ? 'selected' : '' ?>>Thí sinh trúng tuyển</option>
                                <option value="da_nhap_hoc" <?= $selectedCriterion === 'da_nhap_hoc' ? 'selected' : '' ?>>Đã xác nhận nhập học</option>
                                <option value="truot" <?= $selectedCriterion === 'truot' ? 'selected' : '' ?>>Thí sinh không trúng tuyển</option>
                            </select>
                        </div>

                        <div>
                            <button class="btn btn-primary" type="submit">🔍 Tổng hợp dữ liệu</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Các thẻ KPI thống kê tổng hợp -->
            <div class="stat-grid">
                <div class="stat-card">
                    <span>Tổng chỉ tiêu tuyển sinh</span>
                    <strong><?= number_format($stats['tong_chi_tieu']) ?></strong>
                    <small>Còn lại: <b><?= number_format($stats['chi_tieu_con_lai']) ?></b> chỉ tiêu</small>
                    <div class="stat-icon">🎯</div>
                </div>

                <div class="stat-card">
                    <span>Tổng hồ sơ nộp dự tuyển</span>
                    <strong><?= number_format($stats['tong_ho_so']) ?></strong>
                    <small>NV1: <?= $stats['so_nv1'] ?> | NV2: <?= $stats['so_nv2'] ?> | NV3: <?= $stats['so_nv3'] ?></small>
                    <div class="stat-icon">📝</div>
                </div>

                <div class="stat-card">
                    <span>Tỷ lệ chọi (Hồ sơ / Chỉ tiêu)</span>
                    <strong style="color: #245cc5;">1 : <?= $stats['ty_le_choi'] ?></strong>
                    <small>Đủ điều kiện: <?= $stats['du_dieu_kien'] ?> hồ sơ</small>
                    <div class="stat-icon">⚖️</div>
                </div>

                <div class="stat-card">
                    <span>Trúng tuyển & Đã nhập học</span>
                    <strong style="color: #13764f;"><?= $stats['da_nhap_hoc'] ?> / <?= $stats['so_trung_tuyen'] ?></strong>
                    <small>Tỷ lệ nhập học: <b><?= $stats['ty_le_nhap_hoc'] ?>%</b></small>
                    <div class="stat-icon">🎓</div>
                </div>
            </div>

            <!-- Thông tin điểm chuẩn và phân bổ trúng tuyển -->
            <?php if ($stats['target']): ?>
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Điểm chuẩn và phân bổ nguyện vọng trúng tuyển</h2>
                        <p class="muted"><?= h($stats['target']['ten_truong']) ?> — Năm học <?= (int)$selectedYear ?></p>
                    </div>
                </div>
                <div style="padding: 20px 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                    <div style="border: 1px solid var(--line); border-radius: 12px; padding: 16px; background: #fafcff;">
                        <span class="pill pill-green" style="margin-bottom: 8px;">Nguyện vọng 1</span>
                        <div style="font-size: 24px; font-weight: 800; color: #18623e; margin: 4px 0;">
                            <?= number_format((float)$stats['target']['diem_chuan_nv1'], 2) ?> điểm
                        </div>
                        <small style="color: var(--muted);">Trúng tuyển: <b><?= $stats['trung_tuyen_nv1'] ?></b> thí sinh</small>
                    </div>

                    <div style="border: 1px solid var(--line); border-radius: 12px; padding: 16px; background: #fafcff;">
                        <span class="pill pill-blue" style="margin-bottom: 8px;">Nguyện vọng 2</span>
                        <div style="font-size: 24px; font-weight: 800; color: #245cc5; margin: 4px 0;">
                            <?= number_format((float)$stats['target']['diem_chuan_nv2'], 2) ?> điểm
                        </div>
                        <small style="color: var(--muted);">Trúng tuyển: <b><?= $stats['trung_tuyen_nv2'] ?></b> thí sinh</small>
                    </div>

                    <div style="border: 1px solid var(--line); border-radius: 12px; padding: 16px; background: #fafcff;">
                        <span class="pill pill-amber" style="margin-bottom: 8px;">Nguyện vọng 3</span>
                        <div style="font-size: 24px; font-weight: 800; color: #a26b0c; margin: 4px 0;">
                            <?= number_format((float)$stats['target']['diem_chuan_nv3'], 2) ?> điểm
                        </div>
                        <small style="color: var(--muted);">Trúng tuyển: <b><?= $stats['trung_tuyen_nv3'] ?></b> thí sinh</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Bảng danh sách thí sinh chi tiết -->
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Danh sách hồ sơ chi tiết theo bộ lọc</h2>
                        <p class="muted">Hiển thị <?= count($candidates) ?> hồ sơ phù hợp với tiêu chí đã chọn</p>
                    </div>
                    <div>
                        <span class="pill pill-blue"><?= count($candidates) ?> hồ sơ</span>
                    </div>
                </div>

                <?php if (empty($candidates)): ?>
                    <div class="empty">
                        <div class="empty-icon">📂</div>
                        <h3>Chưa có dữ liệu phù hợp</h3>
                        <p>Không tìm thấy hồ sơ nào thuộc trường và tiêu chí thống kê đã chọn.</p>
                    </div>
                <?php else: ?>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>MÃ HỌC SINH</th>
                                    <th>HỌ VÀ TÊN</th>
                                    <th>TRƯỜNG THCS</th>
                                    <th>NGUYỆN VỌNG</th>
                                    <th>ĐIỂM XÉT TUYỂN</th>
                                    <th>HỒ SƠ</th>
                                    <th>KẾT QUẢ TUYỂN SINH</th>
                                    <th>XÁC NHẬN NHẬP HỌC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $idx => $c): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><span class="code-chip"><?= h($c['ma_hoc_sinh']) ?></span></td>
                                        <td><b><?= h($c['ho_ten'] ?? 'Chưa cập nhật tên') ?></b></td>
                                        <td><?= h($c['ten_thcs'] ?? 'THCS Liên kết') ?></td>
                                        <td>
                                            <span class="pill <?= $c['nguyen_vong'] == 1 ? 'pill-green' : ($c['nguyen_vong'] == 2 ? 'pill-blue' : 'pill-amber') ?>">
                                                Nguyện vọng <?= (int)$c['nguyen_vong'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <b style="font-size: 14px; color: #1550b2;"><?= number_format((float)$c['diem_xet_tuyen'], 2) ?></b>
                                        </td>
                                        <td>
                                            <?php if ($c['trang_thai_ho_so'] === 'HopLe'): ?>
                                                <span class="pill pill-green">Hợp lệ</span>
                                            <?php else: ?>
                                                <span class="pill pill-red">Không hợp lệ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($c['ket_qua_tuyen_sinh'] === 'TrungTuyen'): ?>
                                                <span class="pill pill-green">🎉 Trúng tuyển</span>
                                            <?php elseif ($c['ket_qua_tuyen_sinh'] === 'KhongTrungTuyen'): ?>
                                                <span class="pill pill-red">Không trúng tuyển</span>
                                            <?php else: ?>
                                                <span class="pill pill-amber">Đang xét duyệt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($c['xac_nhan_nhap_hoc'] === 'DaXacNhan'): ?>
                                                <span class="pill pill-green">✓ Đã xác nhận</span>
                                                <small class="table-subtext"><?= h($c['thoi_gian_xac_nhan'] ?? '') ?></small>
                                            <?php else: ?>
                                                <span class="pill pill-amber">Chưa xác nhận</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer-note">
                Chức năng UC09: Thống kê kết quả tuyển sinh — Phục vụ Ban tuyển sinh trường THPT (Chế độ chỉ đọc, bảo đảm an toàn dữ liệu).
            </div>
        </div>
    </main>
</div>
</body>
</html>
