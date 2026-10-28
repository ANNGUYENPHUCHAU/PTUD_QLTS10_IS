<?php
$byPriority = [1 => [], 2 => [], 3 => []];
foreach ($results as $result) {
    $byPriority[(int) $result['priority']][] = $result;
}
?>
<nav class="breadcrumb"><a href="index.php">Thực hiện xét tuyển</a><span>/</span><a href="index.php?action=results&amp;run_id=<?= e($run['id']) ?>">Kết quả</a><span>/</span><?= e($school['name']) ?></nav>

<div class="page-heading">
    <div>
        <p class="eyebrow"><?= e($school['code']) ?></p>
        <h1><?= e($school['name']) ?></h1>
        <p class="muted">Chỉ tiêu: <?= e($school['quota']) ?> · Tổng trúng tuyển: <?= e(count($results)) ?> · Còn lại: <?= e(max(0, (int) $school['quota'] - count($results))) ?></p>
    </div>
    <a class="button button--secondary" href="index.php?action=results&amp;run_id=<?= e($run['id']) ?>">Quay lại tổng hợp</a>
</div>

<?php for ($priority = 1; $priority <= 3; $priority++): ?>
    <section class="card">
        <div class="card__header">
            <div>
                <h2>Danh sách trúng tuyển NV<?= e($priority) ?></h2>
                <p class="muted"><?= e(count($byPriority[$priority])) ?> học sinh</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>STT</th><th>Số báo danh</th><th>Họ và tên</th><th>Điểm xét tuyển</th><th>Nguyện vọng</th></tr></thead>
                <tbody>
                <?php if ($byPriority[$priority] === []): ?>
                    <tr><td colspan="5" class="empty">Không có học sinh trúng tuyển ở nguyện vọng này.</td></tr>
                <?php else: ?>
                    <?php foreach ($byPriority[$priority] as $index => $student): ?>
                        <tr>
                            <td><?= e($index + 1) ?></td>
                            <td><?= e($student['candidate_number']) ?></td>
                            <td><strong><?= e($student['full_name']) ?></strong></td>
                            <td><?= e(number_format((float) $student['score'], 2, ',', '.')) ?></td>
                            <td><span class="priority priority--<?= e($priority) ?>">NV<?= e($priority) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endfor; ?>

