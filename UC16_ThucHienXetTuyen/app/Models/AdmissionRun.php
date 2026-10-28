<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AdmissionRun
{
    public function __construct(private readonly PDO $db)
    {
    }

    /** @return array<string, mixed>|false */
    public function find(int $id): array|false
    {
        $statement = $this->db->prepare(
            'SELECT ar.*, r.name AS round_name, r.school_year
             FROM admission_runs ar
             JOIN admission_rounds r ON r.id = ar.round_id
             WHERE ar.id = :id'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }

    /** @return list<array<string, mixed>> */
    public function summary(int $runId): array
    {
        $statement = $this->db->prepare(
            'SELECT s.id, s.code, s.name, s.quota,
                    SUM(CASE WHEN ar.priority = 1 THEN 1 ELSE 0 END) AS nv1_count,
                    SUM(CASE WHEN ar.priority = 2 THEN 1 ELSE 0 END) AS nv2_count,
                    SUM(CASE WHEN ar.priority = 3 THEN 1 ELSE 0 END) AS nv3_count,
                    COUNT(ar.id) AS admitted_count,
                    s.quota - COUNT(ar.id) AS remaining_quota,
                    MIN(ar.score) AS cutoff_score
             FROM schools s
             LEFT JOIN admission_results ar ON ar.school_id = s.id AND ar.run_id = :run_id
             GROUP BY s.id, s.code, s.name, s.quota
             ORDER BY s.name'
        );
        $statement->execute(['run_id' => $runId]);
        return $statement->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function schoolResults(int $runId, int $schoolId): array
    {
        $statement = $this->db->prepare(
            'SELECT ar.priority, ar.score, st.candidate_number, st.full_name
             FROM admission_results ar
             JOIN students st ON st.id = ar.student_id
             WHERE ar.run_id = :run_id AND ar.school_id = :school_id
             ORDER BY ar.priority, ar.score DESC, st.candidate_number'
        );
        $statement->execute(['run_id' => $runId, 'school_id' => $schoolId]);
        return $statement->fetchAll();
    }
}

