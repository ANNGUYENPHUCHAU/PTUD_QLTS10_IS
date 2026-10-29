<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dữ liệu chấm thi · UC19</title><link rel="stylesheet" href="assets/uc19.css"></head>
<body><main class="container narrow">
    <p class="eyebrow">UC19 / DỮ LIỆU PHỤC VỤ CHẤM THI</p>
    <h1>Danh sách số phách</h1>
    <p class="muted">Chỉ sử dụng số phách để nhận diện bài thi.</p>
    <section class="card">
        <h2><?= e($bag['code'] ?? 'Chưa chọn túi chấm') ?></h2>
        <p><?= e($bag['exam_name'] ?? '') ?> · <?= e($bag['subject_name'] ?? '') ?></p>
        <table aria-label="Dữ liệu chấm thi"><thead><tr><th>STT</th><th>Số phách</th><th>Số tờ giấy thi</th></tr></thead>
        <tbody><?php foreach ($numbers as $i=>$row): ?><tr><td><?= $i+1 ?></td><td class="code"><?= e($row['number']) ?></td><td><?= e($row['sheet_count']) ?></td></tr><?php endforeach ?>
        <?php if (!$numbers): ?><tr><td colspan="3">Chưa có dữ liệu phách đã lưu.</td></tr><?php endif ?></tbody></table>
    </section>
    <?php if ($user['can_view_mapping']): ?><a class="button secondary" href="<?= e(uc19Url($context + ['bag'=>$bagId])) ?>">Quay lại quản lý phách</a><?php endif ?>
</main></body></html>
