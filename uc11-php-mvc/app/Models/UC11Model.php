<?php
declare(strict_types=1);

final class UC11Model
{
    public function __construct(private PDO $db)
    {
        $schema = file_get_contents(dirname(__DIR__, 2) . '/database/uc11_demo.sql');
        if ($schema === false) {
            throw new RuntimeException('Không đọc được tệp khởi tạo CSDL.');
        }
        $this->db->exec($schema);
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM rounds ORDER BY opens, id')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rounds WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function logs(): array
    {
        return $this->db->query('SELECT * FROM audit_logs ORDER BY id DESC LIMIT 50')->fetchAll();
    }

    public function transaction(callable $work): string
    {
        // Giữ khóa ghi trước khi đọc để hai yêu cầu không cùng vượt kiểm tra trùng lịch.
        $this->db->exec('BEGIN IMMEDIATE');
        try {
            $message = $work();
            $this->db->exec('COMMIT');
            return $message;
        } catch (Throwable $error) {
            $this->db->exec('ROLLBACK');
            throw $error;
        }
    }

    public function record(?int $id, string $action, string $actor, ?DateTimeImmutable $now = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO audit_logs(round_id, action, actor, occurred_at) VALUES(?,?,?,?)');
        $stmt->execute([$id, $action, $actor, ($now ?? new DateTimeImmutable())->format('Y-m-d H:i:s')]);
    }


    public function conflict(int $exceptId, string $opens, string $confirmation): ?array
    {
        $stmt = $this->db->prepare('SELECT name FROM rounds WHERE id <> ? AND opens <= ? AND confirmation >= ? LIMIT 1');
        $stmt->execute([$exceptId, $confirmation, $opens]);
        return $stmt->fetch() ?: null;
    }
    public function insert(string $name, array $values): int
    {
        $stmt = $this->db->prepare('INSERT INTO rounds(name, opens, closes, selection, confirmation) VALUES(?,?,?,?,?)');
        $stmt->execute([$name, ...$values]);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, string $name, array $values): void
    {
        $stmt = $this->db->prepare('UPDATE rounds SET name=?, opens=?, closes=?, selection=?, confirmation=?, version=version+1 WHERE id=?');
        $stmt->execute([$name, ...$values, $id]);
    }
    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE rounds SET status=?, version=version+1 WHERE id=?');
        $stmt->execute([$status, $id]);
    }
}
