<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Database;
use App\Models\AdmissionRun;
use App\Services\AdmissionService;

require dirname(__DIR__) . '/app/Core/Autoloader.php';
Autoloader::register();

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec('PRAGMA foreign_keys = ON');
Database::setConnection($db);
$db->exec((string) file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
$db->exec((string) file_get_contents(dirname(__DIR__) . '/database/seed.sql'));

$service = new AdmissionService($db);
$runId = $service->run(1, 'Test Officer');
$results = new AdmissionRun($db);
$summary = $results->summary($runId);

$total = array_sum(array_map(static fn (array $row): int => (int) $row['admitted_count'], $summary));
if ($total !== 12) {
    throw new RuntimeException("Sai tổng số trúng tuyển: dự kiến 12, nhận {$total}.");
}

$priorityCounts = $db->query(
    'SELECT priority, COUNT(*) AS total FROM admission_results GROUP BY priority ORDER BY priority'
)->fetchAll(PDO::FETCH_KEY_PAIR);
if ((int) ($priorityCounts[1] ?? 0) !== 9 || (int) ($priorityCounts[2] ?? 0) !== 1 || (int) ($priorityCounts[3] ?? 0) !== 2) {
    throw new RuntimeException('Kết quả phân bổ NV1/NV2/NV3 không đúng dữ liệu kiểm thử.');
}

$duplicates = (int) $db->query(
    'SELECT COUNT(*) FROM (SELECT student_id FROM admission_results GROUP BY run_id, student_id HAVING COUNT(*) > 1) x'
)->fetchColumn();
if ($duplicates !== 0) {
    throw new RuntimeException('Một học sinh đã trúng tuyển nhiều hơn một nguyện vọng.');
}

foreach ($summary as $school) {
    if ((int) $school['admitted_count'] > (int) $school['quota']) {
        throw new RuntimeException('Số trúng tuyển vượt chỉ tiêu tại ' . $school['name']);
    }
}

$service->confirm($runId);
$confirmed = $results->find($runId);
if (!$confirmed || $confirmed['status'] !== 'confirmed') {
    throw new RuntimeException('Không xác nhận được kết quả xét tuyển.');
}

echo "PASS: xét NV1-NV3, không trùng học sinh, không vượt chỉ tiêu và xác nhận thành công.\n";

