<?php
declare(strict_types=1);

class Eligibility
{
    public static function all(): array
    {
        $pdo = get_db();
        $statement = $pdo->query(
            'SELECT e.*, r.rank_name, r.rank_abbr, d.duty_name, s.subduty_name
             FROM rank_duty_eligibility e
             LEFT JOIN ranks r ON r.id = e.rank_id
             LEFT JOIN duties d ON d.id = e.duty_id
             LEFT JOIN duty_subduties s ON s.id = e.subduty_id
             ORDER BY e.duty_id, e.rank_id, e.priority ASC'
        );
        return $statement->fetchAll() ?: [];
    }

    public static function allForDuty(int $dutyId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT e.*, r.rank_name, r.rank_abbr
             FROM rank_duty_eligibility e
             LEFT JOIN ranks r ON r.id = e.rank_id
             WHERE e.duty_id = :duty_id
             ORDER BY e.priority ASC, e.rank_id ASC'
        );
        $statement->execute([':duty_id' => $dutyId]);
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM rank_duty_eligibility WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO rank_duty_eligibility (rank_id, duty_id, subduty_id, priority, eligibility, is_active)
             VALUES (:rank_id, :duty_id, :subduty_id, :priority, :eligibility, :is_active)'
        );

        $statement->execute([
            ':rank_id' => (int) ($data['rank_id'] ?? 0),
            ':duty_id' => (int) ($data['duty_id'] ?? 0),
            ':subduty_id' => !empty($data['subduty_id']) ? (int) $data['subduty_id'] : null,
            ':priority' => (int) ($data['priority'] ?? 0),
            ':eligibility' => isset($data['eligibility']) ? (string) $data['eligibility'] : 'Primary',
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE rank_duty_eligibility SET rank_id = :rank_id, duty_id = :duty_id, subduty_id = :subduty_id, priority = :priority, eligibility = :eligibility, is_active = :is_active WHERE id = :id'
        );

        return $statement->execute([
            ':rank_id' => (int) ($data['rank_id'] ?? 0),
            ':duty_id' => (int) ($data['duty_id'] ?? 0),
            ':subduty_id' => !empty($data['subduty_id']) ? (int) $data['subduty_id'] : null,
            ':priority' => (int) ($data['priority'] ?? 0),
            ':eligibility' => isset($data['eligibility']) ? (string) $data['eligibility'] : 'Primary',
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM rank_duty_eligibility WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }
}
