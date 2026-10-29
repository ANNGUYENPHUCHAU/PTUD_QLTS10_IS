<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UC09: Thống kê kết quả tuyển sinh | QLTS Lớp 10</title>
    <link rel="stylesheet" href="assets/uc09.css">
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

            <!-- Các chỉ số thống kê tổng quan (Cards) -->
            <div class="stat-grid">
                <div class="stat-card">
                    <span>Tổng chỉ tiêu tuyển sinh</span>
                    <strong><?= number_format($stats['tong_chi_tieu']) ?></strong>
                    <small>Còn lại: <b><?= number_format($stats['chi_tieu_con_lai']) ?></b> chỉ tiêu</small>
                    <div class="stat-icon">🎯</div>
                </div>

                <div class="stat-card">
                    <span>Tổng hồ sơ nộp</span>
                    <strong><?= number_format($stats['tong_ho_so']) ?></strong>
                    <small>Đủ điều kiện: <?= number_format($stats['du_dieu_kien']) ?> hồ sơ</small>
                    <div class="stat-icon">📄</div>
                </div>

                <div class="stat-card">
                    <span>Tỷ lệ chọi (Hồ sơ/Chỉ tiêu)</span>
                    <strong style="color: var(--blue);">1 : <?= number_format($stats['ty_le_choi'], 2) ?></strong>
                    <small><?= $stats['ty_le_choi'] >= 1.5 ? '⚠️ Tỷ lệ cạnh tranh cao' : '✅ Tỷ lệ trung bình' ?></small>
                    <div class="stat-icon">⚖️</div>
                </div>

                <div class="stat-card">
                    <span>Đã xác nhận nhập học</span>
                    <strong style="color: var(--green);"><?= number_format($stats['da_nhap_hoc']) ?></strong>
                    <small>Đạt <b><?= $stats['ty_le_nhap_hoc'] ?>%</b> số trúng tuyển</small>
                    <div class="stat-icon">🎓</div>
                </div>
            </div>

            <!-- Phân tích chi tiết nguyện vọng & Điểm chuẩn -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px;">
                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Phân bố hồ sơ & trúng tuyển theo nguyện vọng</h2>
                            <p class="muted">Tình hình hồ sơ đăng ký và thí sinh đạt điểm chuẩn từng nguyện vọng</p>
                        </div>
                    </div>
                    <div style="padding: 22px 25px;">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>NGUYỆN VỌNG</th>
                                    <th>SỐ HỒ SƠ ĐĂNG KÝ</th>
                                    <th>SỐ LƯỢNG TRÚNG TUYỂN</th>
                                    <th>TỶ LỆ TRÚNG TUYỂN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><b>Nguyện vọng 1 (NV1)</b></td>
                                    <td><?= number_format($stats['so_nv1']) ?> hồ sơ</td>
                                    <td><span class="pill pill-green"><?= number_format($stats['trung_tuyen_nv1']) ?> thí sinh</span></td>
                                    <td><?= $stats['so_nv1'] > 0 ? round(($stats['trung_tuyen_nv1'] / $stats['so_nv1']) * 100, 1) : 0 ?>%</td>
                                </tr>
                                <tr>
                                    <td><b>Nguyện vọng 2 (NV2)</b></td>
                                    <td><?= number_format($stats['so_nv2']) ?> hồ sơ</td>
                                    <td><span class="pill pill-blue"><?= number_format($stats['trung_tuyen_nv2']) ?> thí sinh</span></td>
                                    <td><?= $stats['so_nv2'] > 0 ? round(($stats['trung_tuyen_nv2'] / $stats['so_nv2']) * 100, 1) : 0 ?>%</td>
                                </tr>
                                <tr>
                                    <td><b>Nguyện vọng 3 (NV3)</b></td>
                                    <td><?= number_format($stats['so_nv3']) ?> hồ sơ</td>
                                    <td><span class="pill pill-amber"><?= number_format($stats['trung_tuyen_nv3']) ?> thí sinh</span></td>
                                    <td><?= $stats['so_nv3'] > 0 ? round(($stats['trung_tuyen_nv3'] / $stats['so_nv3']) * 100, 1) : 0 ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Điểm chuẩn năm <?= (int)$selectedYear ?></h2>
                            <p class="muted">Điểm trúng tuyển theo quy định</p>
                        </div>
                    </div>
                    <div style="padding: 22px 25px;">
                        <?php if ($stats['target']): ?>
                            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--line);">
                                <span>Điểm chuẩn NV1:</span>
                                <b style="font-size: 16px; color: var(--blue);"><?= number_format((float)$stats['target']['diem_chuan_nv1'], 2) ?></b>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--line);">
                                <span>Điểm chuẩn NV2:</span>
                                <b style="font-size: 16px; color: var(--blue);"><?= number_format((float)$stats['target']['diem_chuan_nv2'], 2) ?></b>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px 0;">
                                <span>Điểm chuẩn NV3:</span>
                                <b style="font-size: 16px; color: var(--blue);"><?= number_format((float)$stats['target']['diem_chuan_nv3'], 2) ?></b>
                            </div>
                        <?php else: ?>
                            <div class="empty">
                                <p>Chưa có dữ liệu điểm chuẩn cho năm này.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách thí sinh -->
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Danh sách thí sinh & Kết quả tuyển sinh (<?= count($candidates) ?> kết quả)</h2>
                        <p class="muted">Hiển thị thông tin hồ sơ và kết quả xét tuyển theo điều kiện đã lọc</p>
                    </div>
                    <div>
                        <a href="uc09.php?action=export_csv&school=<?= urlencode($selectedSchool) ?>&year=<?= (int)$selectedYear ?>&criterion=<?= urlencode($selectedCriterion) ?>" class="btn btn-outline">
                            📥 Xuất báo cáo (CSV)
                        </a>
                    </div>
                </div>

                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>MÃ THÍ SINH</th>
                                <th>HỌ VÀ TÊN</th>
                                <th>TRƯỜNG THCS</th>
                                <th>NGUYỆN VỌNG</th>
                                <th>ĐIỂM XÉT TUYỂN</th>
                                <th>TRẠNG THÁI HỒ SƠ</th>
                                <th>KẾT QUẢ XÉT TUYỂN</th>
                                <th>XÁC NHẬN NHẬP HỌC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($candidates)): ?>
                                <?php $i = 1; foreach ($candidates as $c): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><span class="code-chip"><?= h($c['ma_hoc_sinh']) ?></span></td>
                                        <td><b><?= h($c['ho_ten'] ?? 'Chưa cập nhật') ?></b></td>
                                        <td><?= h($c['ten_thcs'] ?? 'N/A') ?></td>
                                        <td><span class="pill pill-blue">NV<?= (int)$c['nguyen_vong'] ?></span></td>
                                        <td><strong style="color: var(--blue); font-size: 14px;"><?= number_format((float)$c['diem_xet_tuyen'], 2) ?></strong></td>
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
                                                <span class="pill pill-amber">Đang xét</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($c['xac_nhan_nhap_hoc'] === 'DaXacNhan'): ?>
                                                <span class="pill pill-green">Đã xác nhận</span>
                                                <?php if (!empty($c['thoi_gian_xac_nhan'])): ?>
                                                    <small class="table-subtext"><?= h($c['thoi_gian_xac_nhan']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="pill pill-amber">Chưa xác nhận</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: var(--muted);">
                                        Không tìm thấy thí sinh nào phù hợp với bộ lọc hiện tại.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer-note">
                Chức năng UC09: Thống kê kết quả tuyển sinh — Phục vụ Ban tuyển sinh trường THPT (Chế độ chỉ đọc, bảo đảm an toàn dữ liệu).
            </div>
        </div>
    </main>
</div>
</body>
</html>
