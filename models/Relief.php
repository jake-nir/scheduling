<?php
declare(strict_types=1);

class Relief
{
    public static function allForSubDuty(int $subDutyId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duty_reliefs WHERE subduty_id = :subduty_id ORDER BY sort_order IS NULL, sort_order ASC, relief_order ASC, id ASC');
        $statement->execute([':subduty_id' => $subDutyId]);
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duty_reliefs WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $relief = $statement->fetch();
        return $relief ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO duty_reliefs (subduty_id, relief_name, relief_order, sort_order, is_active)
             VALUES (:subduty_id, :relief_name, :relief_order, :sort_order, :is_active)'
        );

        $statement->execute([
            ':subduty_id' => (int) ($data['subduty_id'] ?? 0),
            ':relief_name' => trim((string) ($data['relief_name'] ?? '')),
            ':relief_order' => (int) ($data['relief_order'] ?? 0),
            ':sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE duty_reliefs SET subduty_id = :subduty_id, relief_name = :relief_name, relief_order = :relief_order, sort_order = :sort_order, is_active = :is_active WHERE id = :id'
        );

        return $statement->execute([
            ':subduty_id' => (int) ($data['subduty_id'] ?? 0),
            ':relief_name' => trim((string) ($data['relief_name'] ?? '')),
            ':relief_order' => (int) ($data['relief_order'] ?? 0),
            ':sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM duty_reliefs WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }
}
