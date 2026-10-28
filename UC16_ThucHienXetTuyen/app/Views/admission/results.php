<?php
$totalQuota = array_sum(array_map(static fn (array $item): int => (int) $item['quota'], $summary));
$totalAdmitted = array_sum(array_map(static fn (array $item): int => (int) $item['admitted_count'], $summary));
?>
<nav class="breadcrumb"><a href="index.php">Thực hiện xét tuyển</a><span>/</span>Kết quả</nav>

<div class="page-heading">
    <div>
        <p class="eyebrow"><?= e($run['round_name']) ?> · <?= e($run['school_year']) ?></p>
        <h1>Kết quả xét tuyển</h1>
        <p class="muted">Tạo lúc <?= e(date('H:i d/m/Y', strtotime($run['created_at']))) ?> bởi <?= e($run['created_by']) ?>.</p>
    </div>
    <?php if ($run['status'] === 'confirmed'): ?>
        <span class="status status--confirmed">Đã xác nhận</span>
    <?php elseif ($run['status'] === 'draft'): ?>
        <span class="status status--draft">Chờ xác nhận</span>
    <?php else: ?>
        <span class="status">Đã thay thế</span>
    <?php endif; ?>
</div>

<div class="metrics">
    <article class="metric"><span>Tổng chỉ tiêu</span><strong><?= e($totalQuota) ?></strong></article>
    <article class="metric"><span>Đã trúng tuyển</span><strong><?= e($totalAdmitted) ?></strong></article>
    <article class="metric"><span>Chỉ tiêu còn lại</span><strong><?= e(max(0, $totalQuota - $totalAdmitted)) ?></strong></article>
    <article class="metric"><span>Số trường</span><strong><?= e(count($summary)) ?></strong></article>
</div>

<section class="card">
    <div class="card__header">
        <div>
            <h2>Tổng hợp theo trường</h2>
            <p class="muted">Chọn một trường để xem danh sách học sinh theo từng nguyện vọng.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Trường THPT</th>
                <th>Chỉ tiêu</th>
                <th>NV1</th>
                <th>NV2</th>
                <th>NV3</th>
                <th>Tổng trúng tuyển</th>
                <th>Còn lại</th>
                <th>Điểm thấp nhất</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($summary as $item): ?>
                <tr>
                    <td><strong><?= e($item['name']) ?></strong><small class="block muted"><?= e($item['code']) ?></small></td>
                    <td><?= e($item['quota']) ?></td>
                    <td><?= e($item['nv1_count']) ?></td>
                    <td><?= e($item['nv2_count']) ?></td>
                    <td><?= e($item['nv3_count']) ?></td>
                    <td><strong><?= e($item['admitted_count']) ?></strong></td>
                    <td><?= e($item['remaining_quota']) ?></td>
                    <td><?= $item['cutoff_score'] !== null ? e(number_format((float) $item['cutoff_score'], 2, ',', '.')) : '—' ?></td>
                    <td><a class="button button--ghost" href="index.php?action=school&amp;run_id=<?= e($run['id']) ?>&amp;school_id=<?= e($item['id']) ?>">Chi tiết</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="action-bar">
    <a class="button button--secondary" href="index.php">Quay lại</a>
    <?php if ($run['status'] === 'draft'): ?>
        <form method="post" action="index.php?action=confirm" onsubmit="return confirm('Xác nhận và lưu kết quả xét tuyển này?');">
            <input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">
            <input type="hidden" name="run_id" value="<?= e($run['id']) ?>">
            <button class="button button--primary" type="submit">Xác nhận kết quả</button>
        </form>
    <?php endif; ?>
</div>

