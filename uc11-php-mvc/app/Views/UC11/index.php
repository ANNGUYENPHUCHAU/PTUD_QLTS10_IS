<div class="toolbar">
<p>Người thực hiện: <strong><?= e($_SESSION['user']) ?></strong></p>
<form method="post" action="uc11.php?action=logout" onsubmit="return confirm('Xác nhận đăng xuất?')">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<button class="secondary">Đăng xuất</button></form></div>
<section>
<h2><?= !empty($current['id']) ? 'Điều chỉnh đợt tuyển sinh' : 'Tạo đợt tuyển sinh mới' ?></h2>
<form method="post" action="uc11.php?action=save">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<input type="hidden" name="id" value="<?= e($current['id'] ?? 0) ?>">
<input type="hidden" name="version" value="<?= e($current['version'] ?? 0) ?>">
<label for="name">Tên đợt tuyển sinh</label>
<input id="name" name="name" maxlength="150" required value="<?= e($current['name'] ?? '') ?>" placeholder="Ví dụ: Đợt tuyển sinh chính thức 2027">
<div class="grid">
<?php foreach (['opens'=>'Mở đăng ký', 'closes'=>'Đóng đăng ký', 'selection'=>'Xét tuyển', 'confirmation'=>'Xác nhận nhập học'] as $key=>$label): ?>
<div><label for="<?= e($key) ?>"><?= e($label) ?></label>
<input id="<?= e($key) ?>" type="datetime-local" name="<?= e($key) ?>" required value="<?= e($current[$key] ?? '') ?>"></div>
<?php endforeach; ?>
</div>
<p class="hint">Các mốc phải tăng dần. Khoảng từ mở đăng ký đến xác nhận nhập học không được giao với đợt khác.</p>
<button>Lưu đợt tuyển sinh</button>
<a class="cancel" href="uc11.php">Hủy</a>
</form></section>
<section><div class="toolbar"><h2>Danh sách đợt tuyển sinh</h2>
<a class="button" href="uc11.php">Tạo đợt mới</a></div>
<?php if (!$rounds): ?><p>Chưa có đợt tuyển sinh. Hãy tạo đợt đầu tiên.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Đợt tuyển sinh</th><th>Mở và đóng đăng ký</th><th>Xét tuyển và xác nhận</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach ($rounds as $round): ?>
<tr><td><strong><?= e($round['name']) ?></strong><small>Mã đợt #<?= e($round['id']) ?></small></td>
<td><?= e(str_replace('T', ' ', $round['opens'])) ?><small>đến <?= e(str_replace('T', ' ', $round['closes'])) ?></small></td>
<td><?= e(str_replace('T', ' ', $round['selection'])) ?><small><?= e(str_replace('T', ' ', $round['confirmation'])) ?></small></td>
<td><span class="badge"><?= e($round['status']) ?></span></td>
<td><a href="uc11.php?action=edit&amp;id=<?= e($round['id']) ?>">Sửa lịch</a>
<div class="actions"><?php foreach (['open'=>'Mở đợt', 'close'=>'Đóng đợt'] as $action=>$label): ?>
<form method="post" action="uc11.php?action=<?= e($action) ?>">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<input type="hidden" name="id" value="<?= e($round['id']) ?>">
<input type="hidden" name="version" value="<?= e($round['version']) ?>">
<button class="secondary"><?= e($label) ?></button></form>
<?php endforeach; ?></div></td></tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<section><h2>Nhật ký thao tác</h2>
<?php if (!$logs): ?><p>Chưa có thao tác.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Thời điểm</th><th>Người thực hiện</th><th>Đợt</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach ($logs as $log): ?><tr>
<td><?= e($log['occurred_at']) ?></td><td><?= e($log['actor']) ?></td>
<td><?= $log['round_id'] !== null ? '#' . e($log['round_id']) : '—' ?></td>
<td><?= e($log['action']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
