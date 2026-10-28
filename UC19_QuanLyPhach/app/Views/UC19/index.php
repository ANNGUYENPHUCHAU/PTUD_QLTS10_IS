<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UC19 · Quản lý phách bài thi</title><link rel="stylesheet" href="assets/uc19.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="uc19.php"><span class="brand-mark">TS</span> Tuyển sinh lớp 10</a>
    <div class="account"><span><?= e($user['full_name']) ?> <small>· Thư ký</small></span>
        <form method="post"><?php formFields('logout') ?><button class="quiet">Đăng xuất</button></form>
    </div>
</header>
<main class="container">
    <div class="page-heading">
        <div><p class="eyebrow">NGHIỆP VỤ KHẢO THÍ / UC19</p><h1>Quản lý phách bài thi</h1></div>
        <span class="badge">Môn tự luận · Túi chấm từ nhiều phòng</span>
    </div>
    <?php if ($notice): ?><div role="alert" class="notice <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif ?>
    <section class="card">
        <h2><span class="step">1</span> Chọn kỳ thi và môn thi</h2>
        <form method="get" class="filters">
            <?php foreach ([['exam','Kỳ thi',$catalog,'exam_id','exam_name',$exam], ['subject','Môn thi tự luận',$examRows,'subject_id','subject_name',$subject]] as [$key,$label,$options,$idKey,$nameKey,$selected]): ?>
            <label><?= e($label) ?><select name="<?= e($key) ?>" data-filter="<?= e($key) ?>">
                <?php $seen=[]; foreach ($options as $option): if(isset($seen[$option[$idKey]])) continue; $seen[$option[$idKey]]=true; ?>
                <option value="<?= e($option[$idKey]) ?>" <?= (int)$option[$idKey] === $selected ? 'selected' : '' ?>><?= e($option[$nameKey]) ?></option>
                <?php endforeach ?>
            </select></label>
            <?php endforeach ?>
            <button class="primary">Xem túi chấm</button>
        </form>
    </section>

    <?php if (!$bags): ?>
        <section class="card"><p class="empty" role="status">Không có túi chấm thuộc kỳ thi và môn thi đã chọn</p></section>
    <?php else: ?>
    <div class="workspace">
        <section class="card">
            <div class="section-heading"><h2><span class="step">2</span> Danh sách túi chấm</h2><span class="count"><?= count($bags) ?> túi</span></div>
            <div class="bag-list">
                <?php foreach ($bags as $item): ?>
                <a class="bag <?= (int)$item['id'] === $bagId ? 'active' : '' ?>" href="<?= e(uc19Url($context + ['bag'=>$item['id']])) ?>">
                    <strong><?= e($item['code']) ?></strong>
                    <span><?= e($item['paper_count'] ?? '?') ?> bài · <?= e($item['sheet_count'] ?? '?') ?> tờ</span>
                    <span class="status <?= e($item['status']) ?>"><?= $item['status']==='created' ? 'Đã tạo phách' : 'Chưa tạo phách' ?></span>
                </a>
                <?php endforeach ?>
            </div>
        </section>
        <section class="card" aria-label="Thông tin túi chấm">
            <h2><span class="step">3</span> <?= $draft ? 'Kiểm tra số phách dự kiến' : 'Thông tin túi chấm' ?></h2>
            <div class="bag-heading"><h3><?= e($bag['code']) ?></h3><span class="status <?= e($bag['status']) ?>"><?= $bag['status']==='created' ? 'Đã tạo phách' : 'Chưa tạo phách' ?></span></div>
            <dl class="details">
                <div><dt>Kỳ thi · Môn tự luận</dt><dd><?= e($bag['exam_name']) ?> · <?= e($bag['subject_name']) ?></dd></div>
                <div><dt>Biên bản bàn giao</dt><dd><?= e($bag['handover_ref'] ?? 'Chưa có') ?></dd></div>
                <div><dt>Theo biên bản bàn giao</dt><dd><?= e($bag['paper_count'] ?? '?') ?> bài · <?= e($bag['sheet_count'] ?? '?') ?> tờ giấy thi</dd></div>
                <div><dt>Theo danh sách trong túi</dt><dd><?= count($entries) ?> bài · <?= array_sum(array_column($entries,'sheet_count')) ?> tờ giấy thi</dd></div>
            </dl>
            <div class="table-wrap">
                <table aria-label="Danh sách bài thi trong túi">
                    <thead><tr><th>Số báo danh</th><th>Phòng thi</th><th>Số tờ giấy thi</th><?php if ($draft): ?><th>Số phách dự kiến</th><th>Thao tác</th><?php endif ?></tr></thead>
                    <tbody>
                    <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= e((string)($entry['candidate_number'] ?? '') ?: 'Thiếu đối chiếu') ?></td>
                        <td><?= e((string)($entry['room_name'] ?? '') ?: 'Chưa xác định') ?></td>
                        <td><?= e((string)($entry['sheet_count'] ?? '') ?: 'Chưa xác định') ?></td>
                        <?php if ($draft): ?>
                        <td class="code"><?= e($draft['codes'][$entry['id']] ?? '—') ?></td>
                        <td><form method="post"><?php formFields('regenerate') ?>
                            <input type="hidden" name="bag_id" value="<?= $bagId ?>">
                            <input type="hidden" name="draft_token" value="<?= e($draft['token']) ?>">
                            <input type="hidden" name="handover_id" value="<?= e($entry['id']) ?>">
                            <button class="quiet" type="submit">Tạo lại</button>
                        </form></td>
                        <?php endif ?>
                    </tr>
                    <?php endforeach ?>
                    <?php if (!$entries): ?><tr><td colspan="<?= $draft ? 5 : 3 ?>" class="empty">Túi chấm chưa có bài thi.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>

            <div class="actions">
            <?php if ($bag['status']==='created'): ?>
                <span class="muted">Túi đã tạo phách, không cho phép tạo lại.</span>
                <a class="button primary" href="<?= e(uc19Url($context + ['bag'=>$bagId,'view'=>'grading'])) ?>">Xem dữ liệu phục vụ chấm thi</a>
            <?php elseif ($draft): ?>
                <?php foreach (['confirm'=>'Xác nhận lưu','cancel'=>'Hủy'] as $action=>$label): ?>
                <form method="post"><?php formFields($action) ?>
                    <input type="hidden" name="bag_id" value="<?= $bagId ?>">
                    <input type="hidden" name="draft_token" value="<?= e($draft['token']) ?>">
                    <button class="<?= $action==='confirm' ? 'primary' : 'secondary' ?>"><?= e($label) ?></button>
                </form>
                <?php endforeach ?>
            <?php else: ?>
                <form method="post"><?php formFields('generate') ?><input type="hidden" name="bag_id" value="<?= $bagId ?>"><button class="primary">Tạo số phách</button></form>
            <?php endif ?>
            </div>
            <?php if ($logs): ?>
            <div class="audit"><h2>Nhật ký tạo phách</h2>
            <?php foreach ($logs as $log): ?><p><?= e($log['created_at']) ?> · <?= e($log['full_name']) ?> đã tạo phách cho <?= e($log['paper_count']) ?> bài, <?= e($log['sheet_count']) ?> tờ giấy thi.</p><?php endforeach ?>
            </div>
            <?php endif ?>
        </section>
    </div>
    <?php endif ?>
    <footer>UC19 · Hệ thống quản lý tuyển sinh lớp 10</footer>
</main>
<script src="assets/uc19.js"></script>
</body></html>
