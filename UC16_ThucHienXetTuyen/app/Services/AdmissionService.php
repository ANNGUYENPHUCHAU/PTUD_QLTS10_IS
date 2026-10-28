<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use PDO;
use Throwable;

final class AdmissionService
{
    public function __construct(private readonly PDO $db)
    {
    }

    /**
     * Chạy xét tuyển theo thứ tự NV1 -> NV2 -> NV3.
     * Học sinh đã trúng tuyển ở một NV sẽ không được xét ở NV thấp hơn.
     */
    public function run(int $roundId, string $createdBy): int
    {
        $errors = $this->validateInputData($roundId);
        if ($errors !== []) {
            throw new AdmissionException($errors);
        }

        $this->db->beginTransaction();
        try {
            $deleteDrafts = $this->db->prepare(
                "DELETE FROM admission_runs WHERE round_id = :round_id AND status = 'draft'"
            );
            $deleteDrafts->execute(['round_id' => $roundId]);

            $createRun = $this->db->prepare(
                "INSERT INTO admission_runs (round_id, status, created_by) VALUES (:round_id, 'draft', :created_by)"
            );
            $createRun->execute(['round_id' => $roundId, 'created_by' => $createdBy]);
            $runId = (int) $this->db->lastInsertId();

            $quotas = [];
            foreach ($this->db->query('SELECT id, quota FROM schools')->fetchAll() as $school) {
                $quotas[(int) $school['id']] = (int) $school['quota'];
            }

            $admittedStudents = [];
            $insert = $this->db->prepare(
                'INSERT INTO admission_results (run_id, student_id, school_id, priority, score)
                 VALUES (:run_id, :student_id, :school_id, :priority, :score)'
            );

            for ($priority = 1; $priority <= 3; $priority++) {
                $candidates = $this->candidatesByPriority($roundId, $priority);
                foreach ($candidates as $candidate) {
                    $studentId = (int) $candidate['student_id'];
                    $schoolId = (int) $candidate['school_id'];
                    if (isset($admittedStudents[$studentId]) || ($quotas[$schoolId] ?? 0) <= 0) {
                        continue;
                    }

                    $insert->execute([
                        'run_id' => $runId,
                        'student_id' => $studentId,
                        'school_id' => $schoolId,
                        'priority' => $priority,
                        'score' => $candidate['total_score'],
                    ]);
                    $admittedStudents[$studentId] = true;
                    $quotas[$schoolId]--;
                }
            }

            $this->db->commit();
            return $runId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function confirm(int $runId): void
    {
        $this->db->beginTransaction();
        try {
            $find = $this->db->prepare('SELECT round_id, status FROM admission_runs WHERE id = :id');
            $find->execute(['id' => $runId]);
            $run = $find->fetch();
            if (!$run) {
                throw new AdmissionException(['Không tìm thấy kết quả xét tuyển.']);
            }
            if ($run['status'] !== 'draft') {
                throw new AdmissionException(['Kết quả này đã được xác nhận trước đó.']);
            }

            $supersede = $this->db->prepare(
                "UPDATE admission_runs SET status = 'superseded'
                 WHERE round_id = :round_id AND status = 'confirmed'"
            );
            $supersede->execute(['round_id' => $run['round_id']]);

            $confirm = $this->db->prepare(
                "UPDATE admission_runs SET status = 'confirmed', confirmed_at = CURRENT_TIMESTAMP WHERE id = :id"
            );
            $confirm->execute(['id' => $runId]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** @return list<array<string, mixed>> */
    private function candidatesByPriority(int $roundId, int $priority): array
    {
        $statement = $this->db->prepare(
            'SELECT a.student_id, a.school_id, s.total_score, s.candidate_number
             FROM applications a
             JOIN students s ON s.id = a.student_id
             WHERE a.round_id = :round_id AND a.priority = :priority
             ORDER BY a.school_id, s.total_score DESC, s.candidate_number ASC'
        );
        $statement->execute(['round_id' => $roundId, 'priority' => $priority]);
        return $statement->fetchAll();
    }

    /** @return list<string> */
    private function validateInputData(int $roundId): array
    {
        $errors = [];
        $roundStatement = $this->db->prepare('SELECT * FROM admission_rounds WHERE id = :id');
        $roundStatement->execute(['id' => $roundId]);
        $round = $roundStatement->fetch();
        if (!$round) {
            return ['Không tìm thấy đợt xét tuyển.'];
        }

        $now = new DateTimeImmutable();
        if ($round['status'] !== 'active'
            || $now < new DateTimeImmutable((string) $round['starts_at'])
            || $now > new DateTimeImmutable((string) $round['ends_at'])) {
            $errors[] = 'Đợt xét tuyển không ở trong thời gian thực hiện.';
        }

        $schoolCount = (int) $this->db->query('SELECT COUNT(*) FROM schools')->fetchColumn();
        if ($schoolCount === 0) {
            $errors[] = 'Chưa có dữ liệu trường và chỉ tiêu tuyển sinh.';
        }

        $studentStatement = $this->db->prepare(
            'SELECT COUNT(DISTINCT a.student_id)
             FROM applications a JOIN students s ON s.id = a.student_id
             WHERE a.round_id = :round_id AND s.total_score IS NOT NULL'
        );
        $studentStatement->execute(['round_id' => $roundId]);
        if ((int) $studentStatement->fetchColumn() === 0) {
            $errors[] = 'Chưa có điểm thi hoặc danh sách nguyện vọng của học sinh.';
        }

        $incompleteStatement = $this->db->prepare(
            'SELECT COUNT(*) FROM (
                 SELECT student_id
                 FROM applications
                 WHERE round_id = :round_id
                 GROUP BY student_id
                 HAVING COUNT(DISTINCT priority) < 3
             ) incomplete_students'
        );
        $incompleteStatement->execute(['round_id' => $roundId]);
        $incompleteCount = (int) $incompleteStatement->fetchColumn();
        if ($incompleteCount > 0) {
            $errors[] = "Có {$incompleteCount} học sinh chưa đăng ký đủ NV1, NV2, NV3.";
        }

        return $errors;
    }
}

