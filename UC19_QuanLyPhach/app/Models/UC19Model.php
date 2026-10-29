<?php
declare(strict_types=1);

final class UC19Model
{
    public function __construct(private PDO $db) {}

    private function rows(string $sql, array $params = []): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function user(string $username): ?array
    {
        return $this->rows('SELECT * FROM users WHERE username=?', [$username])[0] ?? null;
    }

    public function authorizedUser(int $id): ?array
    {
        return $this->rows("SELECT id, username, full_name, can_view_mapping FROM users
            WHERE id=? AND active=1 AND role='secretary' AND can_anonymize=1", [$id])[0] ?? null;
    }

    public function requireMapping(int $userId): void
    {
        $user = $this->authorizedUser($userId);
        if (!$user || !$user['can_view_mapping']) {
            throw new DomainException('Bạn không có quyền quản lý phách và xem dữ liệu đối chiếu.');
        }
    }

    public function catalog(): array
    {
        // Lấy cả phạm vi chưa có túi để xử lý ngoại lệ 4.1.
        return $this->rows("SELECT es.id AS scope_id, e.id AS exam_id, e.name AS exam_name,
            s.id AS subject_id, s.name AS subject_name
            FROM exam_subjects es JOIN exams e ON e.id=es.exam_id
            JOIN subjects s ON s.id=es.subject_id WHERE s.type='essay' ORDER BY e.id,s.id");
    }

    public function bags(int $scope): array
    {
        return $this->rows('SELECT id, code, paper_count, sheet_count, status FROM grading_bags WHERE scope_id=? ORDER BY id', [$scope]);
    }

    public function bag(int $id): array
    {
        $bag = $this->rows('SELECT b.*, es.exam_id, es.subject_id, e.name AS exam_name,
            s.name AS subject_name, s.type AS subject_type
            FROM grading_bags b JOIN exam_subjects es ON es.id=b.scope_id
            JOIN exams e ON e.id=es.exam_id JOIN subjects s ON s.id=es.subject_id WHERE b.id=?', [$id])[0] ?? null;
        if (!$bag) throw new DomainException('Không tìm thấy túi chấm.');
        return $bag;
    }

    public function handover(int $bagId, int $userId): array
    {
        $this->requireMapping($userId);
        return $this->rows('SELECT p.id, p.scope_id, p.student_id,
            p.sheet_count, p.room_id, r.name AS room_name, a.candidate_number, a.attendance,
            s.full_name,
            (EXISTS (SELECT 1 FROM handover_entries other
                WHERE other.student_id=p.student_id AND other.scope_id=p.scope_id
                AND other.bag_id<>p.bag_id)) AS conflict
            FROM handover_entries p
            LEFT JOIN students s ON s.id=p.student_id
            LEFT JOIN rooms r ON r.id=p.room_id
            LEFT JOIN exam_candidates a ON a.scope_id=p.scope_id AND a.student_id=p.student_id AND a.room_id=p.room_id
            WHERE p.bag_id=? ORDER BY p.id', [$bagId]);
    }

    public function used(int $scope): array
    {
        return array_column($this->rows('SELECT number FROM papers WHERE scope_id=?', [$scope]), 'number');
    }

    public function grading(int $bagId, int $userId): array
    {
        if (!$this->authorizedUser($userId)) throw new DomainException('Bạn không có quyền truy cập dữ liệu chấm thi.');
        // Allowlist: không lấy SBD, mã bài, thí sinh, phòng hoặc điểm thi.
        return $this->rows("SELECT a.number, a.sheet_count FROM papers a
            JOIN grading_bags b ON b.id=a.bag_id WHERE b.id=? AND b.status='created' ORDER BY a.number", [$bagId]);
    }

    public function logs(int $bagId, int $userId): array
    {
        $this->requireMapping($userId);
        return $this->rows('SELECT l.action, l.paper_count, l.sheet_count, l.created_at, u.full_name
            FROM audit_logs l JOIN users u ON u.id=l.user_id WHERE l.bag_id=? ORDER BY l.id', [$bagId]);
    }

    public function createDraft(int $bagId, int $userId): array
    {
        $this->requireMapping($userId);
        $bag = $this->bag($bagId);
        $entries = $this->handover($bagId, $userId);
        UC19Service::validate($bag, $entries);
        return ['bag_id'=>$bagId, 'fingerprint'=>UC19Service::fingerprint($bag, $entries),
            'codes'=>UC19Service::generate($entries, $this->used((int)$bag['scope_id'])),
            'token'=>bin2hex(random_bytes(16))];
    }

    public function regenerate(array $draft, int $handoverId, int $userId): array
    {
        $bag = $this->bag((int)$draft['bag_id']);
        $entries = $this->handover((int)$bag['id'], $userId);
        UC19Service::validate($bag, $entries);
        $this->validateSnapshot($draft, $bag, $entries);
        if (!isset($draft['codes'][$handoverId])) throw new DomainException('Bài thi không thuộc bản xem trước.');
        $used = array_merge($this->used((int)$bag['scope_id']), array_values($draft['codes']));
        $draft['codes'][$handoverId] = UC19Service::generate([['id'=>$handoverId]], $used)[$handoverId];
        $draft['token'] = bin2hex(random_bytes(16));
        return $draft;
    }

    private function validateSnapshot(array $draft, array $bag, array $entries): void
    {
        if (!hash_equals($draft['fingerprint'], UC19Service::fingerprint($bag, $entries))) {
            throw new DomainException('Dữ liệu túi chấm đã thay đổi. Hãy tạo lại bản xem trước.');
        }
    }

    public function save(array $draft, int $userId): void
    {
        $this->requireMapping($userId);
        $initial = $this->bag((int)$draft['bag_id']);
        $mysql = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
        if ($mysql) $this->db->exec('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        $this->db->beginTransaction();
        try {
            // Tuần tự hóa việc lưu mọi túi trong cùng kỳ/môn; SERIALIZABLE bảo vệ các danh sách đối chiếu.
            $this->rows('SELECT id FROM exam_subjects WHERE id=?' . ($mysql ? ' FOR UPDATE' : ''), [$initial['scope_id']]);
            $this->rows('SELECT id FROM grading_bags WHERE id=?' . ($mysql ? ' FOR UPDATE' : ''), [$initial['id']]);
            $bag = $this->bag((int)$draft['bag_id']);
            if ($bag['scope_id'] !== $initial['scope_id']) throw new DomainException('Phạm vi túi chấm đã thay đổi.');
            $this->requireMapping($userId);
            $entries = $this->handover((int)$bag['id'], $userId);
            UC19Service::validate($bag, $entries);
            $this->validateSnapshot($draft, $bag, $entries);
            UC19Service::validateCodes($entries, $draft['codes'], $this->used((int)$bag['scope_id']));
            $insert = $this->db->prepare('INSERT INTO papers
                (handover_id, scope_id, bag_id, student_id, number, sheet_count) VALUES (?,?,?,?,?,?)');
            foreach ($entries as $entry) {
                $insert->execute([$entry['id'], $bag['scope_id'], $bag['id'], $entry['student_id'],
                    $draft['codes'][$entry['id']], $entry['sheet_count']]);
            }
            $time = date('Y-m-d H:i:s');
            $statement = $this->db->prepare("UPDATE grading_bags SET status='created', created_by=?, created_at=? WHERE id=? AND status='pending'");
            $statement->execute([$userId, $time, $bag['id']]);
            if ($statement->rowCount() !== 1) throw new DomainException('Túi chấm đã được tạo phách');
            $log = $this->db->prepare('INSERT INTO audit_logs (bag_id,user_id,action,paper_count,sheet_count,created_at) VALUES (?,?,?,?,?,?)');
            $log->execute([$bag['id'], $userId, 'CREATE_CODES', count($entries), array_sum(array_column($entries,'sheet_count')), $time]);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }
}
