<?php
declare(strict_types=1);

class Role
{
    public const PERMISSION_KEYS = [
        'dashboard.view',
        'calendar.view',
        'personnel.view',
        'personnel.manage',
        'availability.view',
        'availability.manage',
        'dutyhistory.view',
        'dutyconfig.manage',
        'rotationconfig.view',
        'schedule.manage',
        'rotation.view',
        'pod.manage',
        'pod.revisions',
        'reports.view',
        'users.manage',
        'audit.view',
        'settings.manage',
    ];

    public static function all(): array
    {
        $pdo = get_db();
        $statement = $pdo->query('SELECT * FROM roles ORDER BY id ASC');
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $role = $statement->fetch();
        return $role ?: null;
    }

    public static function findByName(string $name): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM roles WHERE role_name = :role_name LIMIT 1');
        $statement->execute([':role_name' => $name]);
        $role = $statement->fetch();
        return $role ?: null;
    }

    public static function getPermissionsForRoleId(int $roleId): array
    {
        $role = self::findById($roleId);
        if (!$role) {
            return [];
        }

        return self::decodePermissions($role['permissions'] ?? '[]');
    }

    public static function decodePermissions(mixed $permissions): array
    {
        $values = [];

        if (is_array($permissions)) {
            $values = array_values($permissions);
        } else {
            $decoded = json_decode((string) $permissions, true);
            if (is_array($decoded)) {
                $values = array_values($decoded);
            }
        }

        $normalized = [];
        foreach ($values as $value) {
            $normalized[] = normalize_permission((string) $value);
        }

        return array_values(array_unique($normalized));
    }

    public static function hasPermission(?array $role, string $permission): bool
    {
        if (!$role) {
            return false;
        }

        $permissions = self::decodePermissions($role['permissions'] ?? '[]');
        return in_array(normalize_permission($permission), $permissions, true);
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare('INSERT INTO roles (role_name, description, permissions, is_active) VALUES (:role_name, :description, :permissions, :is_active)');
        $statement->execute([
            ':role_name' => trim((string) ($data['role_name'] ?? '')),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':permissions' => json_encode(array_values($data['permissions'] ?? []), JSON_THROW_ON_ERROR),
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE roles SET role_name = :role_name, description = :description, permissions = :permissions, is_active = :is_active WHERE id = :id');
        return $statement->execute([
            ':role_name' => trim((string) ($data['role_name'] ?? '')),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':permissions' => json_encode(array_values($data['permissions'] ?? []), JSON_THROW_ON_ERROR),
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM roles WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }
}
