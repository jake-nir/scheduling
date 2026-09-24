<?php
declare(strict_types=1);

class Duty
{
    public static function all(): array
    {
        $pdo = get_db();
        $statement = $pdo->query('SELECT * FROM duties ORDER BY sort_order IS NULL, sort_order ASC, id ASC');
        return $statement->fetchAll() ?: [];
    }

    public static function active(): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duties WHERE is_active = 1 ORDER BY sort_order IS NULL, sort_order ASC, id ASC');
        $statement->execute();
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duties WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $duty = $statement->fetch();
        return $duty ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM duties WHERE duty_code = :duty_code LIMIT 1');
        $statement->execute([':duty_code' => trim($code)]);
        return $statement->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO duties (duty_code, duty_name, description, start_time, end_time, personnel_required, rotation_type, has_subduties, sort_order, is_active)
             VALUES (:duty_code, :duty_name, :description, :start_time, :end_time, :personnel_required, :rotation_type, :has_subduties, :sort_order, :is_active)'
        );

        $statement->execute([
            ':duty_code' => trim((string) ($data['duty_code'] ?? '')),
            ':duty_name' => trim((string) ($data['duty_name'] ?? '')),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':start_time' => isset($data['start_time']) && (string) $data['start_time'] !== '' ? (string) $data['start_time'] : null,
            ':end_time' => isset($data['end_time']) && (string) $data['end_time'] !== '' ? (string) $data['end_time'] : null,
            ':personnel_required' => (int) ($data['personnel_required'] ?? 1),
            ':rotation_type' => isset($data['rotation_type']) ? (string) $data['rotation_type'] : 'none',
            ':has_subduties' => isset($data['has_subduties']) ? (int) $data['has_subduties'] : 0,
            ':sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE duties SET duty_code = :duty_code, duty_name = :duty_name, description = :description, start_time = :start_time, end_time = :end_time,
             personnel_required = :personnel_required, rotation_type = :rotation_type, has_subduties = :has_subduties, sort_order = :sort_order, is_active = :is_active
             WHERE id = :id'
        );

        return $statement->execute([
            ':duty_code' => trim((string) ($data['duty_code'] ?? '')),
            ':duty_name' => trim((string) ($data['duty_name'] ?? '')),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':start_time' => isset($data['start_time']) && (string) $data['start_time'] !== '' ? (string) $data['start_time'] : null,
            ':end_time' => isset($data['end_time']) && (string) $data['end_time'] !== '' ? (string) $data['end_time'] : null,
            ':personnel_required' => (int) ($data['personnel_required'] ?? 1),
            ':rotation_type' => isset($data['rotation_type']) ? (string) $data['rotation_type'] : 'none',
            ':has_subduties' => isset($data['has_subduties']) ? (int) $data['has_subduties'] : 0,
            ':sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM duties WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function toggleActive(int $id, int $isActive): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE duties SET is_active = :is_active WHERE id = :id');
        return $statement->execute([':is_active' => $isActive, ':id' => $id]);
    }

    public static function subDutyCount(int $dutyId): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT COUNT(*) FROM duty_subduties WHERE duty_id = :duty_id');
        $statement->execute([':duty_id' => $dutyId]);
        return (int) $statement->fetchColumn();
    }

    public static function reliefCount(int $dutyId): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM duty_reliefs dr
             INNER JOIN duty_subduties ds ON ds.id = dr.subduty_id
             WHERE ds.duty_id = :duty_id'
        );
        $statement->execute([':duty_id' => $dutyId]);
        return (int) $statement->fetchColumn();
    }
}
