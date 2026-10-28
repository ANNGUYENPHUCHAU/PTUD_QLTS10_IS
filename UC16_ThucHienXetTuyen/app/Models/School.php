<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class School
{
    public function __construct(private readonly PDO $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->query('SELECT * FROM schools ORDER BY name')->fetchAll();
    }

    /** @return array<string, mixed>|false */
    public function find(int $id): array|false
    {
        $statement = $this->db->prepare('SELECT * FROM schools WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }
}

