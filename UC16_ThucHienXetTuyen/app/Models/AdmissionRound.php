<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AdmissionRound
{
    public function __construct(private readonly PDO $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        $sql = "SELECT r.*,
                       (SELECT COUNT(DISTINCT a.student_id) FROM applications a WHERE a.round_id = r.id) AS student_count,
                       (SELECT COUNT(*) FROM admission_runs ar WHERE ar.round_id = r.id AND ar.status = 'confirmed') AS confirmed_runs
                FROM admission_rounds r
                ORDER BY r.starts_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /** @return array<string, mixed>|false */
    public function find(int $id): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM admission_rounds WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }
}

