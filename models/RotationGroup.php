<?php
declare(strict_types=1);

class RotationGroup
{
    public static function all(): array
    {
        $pdo = get_db();
        $statement = $pdo->query('SELECT * FROM duty_rotation_groups ORDER BY id ASC');
        return $statement->fetchAll() ?: [];
    }

    public static function allForDuty(int $dutyId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duty_rotation_groups WHERE duty_id = :duty_id ORDER BY id ASC');
        $statement->execute([':duty_id' => $dutyId]);
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duty_rotation_groups WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $group = $statement->fetch();
        return $group ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO duty_rotation_groups (duty_id, group_name, current_cycle, current_position, is_active)
             VALUES (:duty_id, :group_name, :current_cycle, :current_position, :is_active)'
        );

        $statement->execute([
            ':duty_id' => (int) ($data['duty_id'] ?? 0),
            ':group_name' => trim((string) ($data['group_name'] ?? '')),
            ':current_cycle' => (int) ($data['current_cycle'] ?? 0),
            ':current_position' => (int) ($data['current_position'] ?? 0),
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE duty_rotation_groups SET duty_id = :duty_id, group_name = :group_name, current_cycle = :current_cycle, current_position = :current_position, is_active = :is_active WHERE id = :id'
        );

        return $statement->execute([
            ':duty_id' => (int) ($data['duty_id'] ?? 0),
            ':group_name' => trim((string) ($data['group_name'] ?? '')),
            ':current_cycle' => (int) ($data['current_cycle'] ?? 0),
            ':current_position' => (int) ($data['current_position'] ?? 0),
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM duty_rotation_groups WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }
}
