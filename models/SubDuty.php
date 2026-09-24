<?php
declare(strict_types=1);

class SubDuty
{
    public static function allForDuty(int $dutyId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duty_subduties WHERE duty_id = :duty_id ORDER BY sort_order IS NULL, sort_order ASC, id ASC');
        $statement->execute([':duty_id' => $dutyId]);
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duty_subduties WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $subDuty = $statement->fetch();
        return $subDuty ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO duty_subduties (duty_id, subduty_code, subduty_name, sort_order, is_active)
             VALUES (:duty_id, :subduty_code, :subduty_name, :sort_order, :is_active)'
        );

        $statement->execute([
            ':duty_id' => (int) ($data['duty_id'] ?? 0),
            ':subduty_code' => trim((string) ($data['subduty_code'] ?? '')),
            ':subduty_name' => trim((string) ($data['subduty_name'] ?? '')),
            ':sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE duty_subduties SET duty_id = :duty_id, subduty_code = :subduty_code, subduty_name = :subduty_name, sort_order = :sort_order, is_active = :is_active WHERE id = :id'
        );

        return $statement->execute([
            ':duty_id' => (int) ($data['duty_id'] ?? 0),
            ':subduty_code' => trim((string) ($data['subduty_code'] ?? '')),
            ':subduty_name' => trim((string) ($data['subduty_name'] ?? '')),
            ':sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM duty_subduties WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function reliefCount(int $subDutyId): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT COUNT(*) FROM duty_reliefs WHERE subduty_id = :subduty_id');
        $statement->execute([':subduty_id' => $subDutyId]);
        return (int) $statement->fetchColumn();
    }
}
