<div class="page-heading">
    <div>
        <p class="eyebrow">Quản lý tuyển sinh</p>
        <h1>Thực hiện xét tuyển</h1>
        <p class="muted">Chọn đợt xét tuyển, kiểm tra chỉ tiêu và bắt đầu xét NV1 → NV2 → NV3.</p>
    </div>
    <span class="status status--active">Đang trong thời gian xét tuyển</span>
</div>

<section class="card">
    <div class="card__header">
        <div>
            <h2>Danh sách đợt xét tuyển</h2>
            <p class="muted">Dữ liệu điểm và nguyện vọng sẽ được kiểm tra trước khi chạy.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Đợt xét tuyển</th>
                <th>Năm học</th>
                <th>Thời gian</th>
                <th>Thí sinh</th>
                <th>Trạng thái</th>
                <th class="align-right">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rounds as $round): ?>
                <tr>
                    <td><strong><?= e($round['name']) ?></strong></td>
                    <td><?= e($round['school_year']) ?></td>
                    <td><?= e(date('d/m/Y', strtotime($round['starts_at']))) ?> – <?= e(date('d/m/Y', strtotime($round['ends_at']))) ?></td>
                    <td><?= e($round['student_count']) ?></td>
                    <td><span class="status status--active">Đang mở</span></td>
                    <td class="align-right">
                        <form method="post" action="index.php?action=run" onsubmit="return confirm('Bắt đầu tính toán kết quả xét tuyển?');">
                            <input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">
                            <input type="hidden" name="round_id" value="<?= e($round['id']) ?>">
                            <button class="button button--primary" type="submit">Xét tuyển</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <div class="card__header">
        <div>
            <h2>Chỉ tiêu các trường THPT công lập</h2>
            <p class="muted"><?= count($schools) ?> trường đã có chỉ tiêu tuyển sinh.</p>
        </div>
    </div>
    <div class="school-grid">
        <?php foreach ($schools as $school): ?>
            <article class="school-card">
                <span class="school-card__code"><?= e($school['code']) ?></span>
                <h3><?= e($school['name']) ?></h3>
                <p><strong><?= e($school['quota']) ?></strong> chỉ tiêu</p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

