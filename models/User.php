<?php
declare(strict_types=1);

class User
{
    public static function all(): array
    {
        $pdo = get_db();
        $statement = $pdo->query(
            'SELECT u.*, r.role_name, r.permissions AS role_permissions
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             ORDER BY u.id ASC'
        );
        $rows = $statement->fetchAll() ?: [];

        foreach ($rows as &$row) {
            $row['permissions'] = Role::decodePermissions($row['role_permissions'] ?? '[]');
        }

        return $rows;
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT u.*, r.role_name, r.permissions AS role_permissions
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $user = $statement->fetch();

        if (!$user) {
            return null;
        }

        $user['permissions'] = Role::decodePermissions($user['role_permissions'] ?? '[]');
        return $user;
    }

    public static function findByUsername(string $username): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT u.*, r.role_name, r.permissions AS role_permissions
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.username = :username
             LIMIT 1'
        );
        $statement->execute([':username' => trim($username)]);
        $user = $statement->fetch();

        if (!$user) {
            return null;
        }

        $user['permissions'] = Role::decodePermissions($user['role_permissions'] ?? '[]');
        return $user;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO users (username, password_hash, full_name, email, role_id, status)
             VALUES (:username, :password_hash, :full_name, :email, :role_id, :status)'
        );

        $statement->execute([
            ':username' => trim((string) ($data['username'] ?? '')),
            ':password_hash' => self::hashPassword((string) ($data['password'] ?? '')),
            ':full_name' => trim((string) ($data['full_name'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':role_id' => (int) ($data['role_id'] ?? 0),
            ':status' => isset($data['status']) ? (string) $data['status'] : 'active',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();

        if (isset($data['password']) && trim((string) $data['password']) !== '') {
            $statement = $pdo->prepare(
                'UPDATE users SET username = :username, full_name = :full_name, email = :email, role_id = :role_id, status = :status, password_hash = :password_hash WHERE id = :id'
            );
            $statement->execute([
                ':username' => trim((string) ($data['username'] ?? '')),
                ':full_name' => trim((string) ($data['full_name'] ?? '')),
                ':email' => trim((string) ($data['email'] ?? '')),
                ':role_id' => (int) ($data['role_id'] ?? 0),
                ':status' => isset($data['status']) ? (string) $data['status'] : 'active',
                ':password_hash' => self::hashPassword((string) $data['password']),
                ':id' => $id,
            ]);
            return true;
        }

        $statement = $pdo->prepare(
            'UPDATE users SET username = :username, full_name = :full_name, email = :email, role_id = :role_id, status = :status WHERE id = :id'
        );

        return $statement->execute([
            ':username' => trim((string) ($data['username'] ?? '')),
            ':full_name' => trim((string) ($data['full_name'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':role_id' => (int) ($data['role_id'] ?? 0),
            ':status' => isset($data['status']) ? (string) $data['status'] : 'active',
            ':id' => $id,
        ]);
    }

    public static function setStatus(int $id, string $status): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
        return $statement->execute([':status' => $status, ':id' => $id]);
    }

    public static function setPassword(int $id, string $password): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        return $statement->execute([
            ':password_hash' => self::hashPassword($password),
            ':id' => $id,
        ]);
    }

    public static function updateLastLogin(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE users SET last_login = NOW(), failed_login_attempts = 0, locked_until = NULL WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function isLockedOut(int $userId): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT failed_login_attempts, locked_until FROM users WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $userId]);
        $row = $statement->fetch();

        if (!$row) {
            return false;
        }

        $attempts = (int) ($row['failed_login_attempts'] ?? 0);
        $lockedUntil = (string) ($row['locked_until'] ?? '');

        if ($attempts < 5 || $lockedUntil === '') {
            return false;
        }

        $lockTime = new DateTimeImmutable($lockedUntil, new DateTimeZone(date_default_timezone_get()));
        return $lockTime > new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
    }

    public static function recordFailedLogin(int $userId): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT failed_login_attempts, locked_until FROM users WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $userId]);
        $row = $statement->fetch();

        if (!$row) {
            return 0;
        }

        $attempts = (int) ($row['failed_login_attempts'] ?? 0);
        $lockedUntil = (string) ($row['locked_until'] ?? '');
        $now = new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));

        if ($lockedUntil !== '') {
            $lockTime = new DateTimeImmutable($lockedUntil, new DateTimeZone(date_default_timezone_get()));
            if ($lockTime > $now) {
                return $attempts;
            }
        }

        $attempts++;
        $lockUntilSql = null;
        if ($attempts >= 5) {
            $lockUntilSql = $now->modify('+15 minutes')->format('Y-m-d H:i:s');
        }

        $pdo->prepare('UPDATE users SET failed_login_attempts = :attempts, locked_until = :locked_until WHERE id = :id')->execute([
            ':attempts' => $attempts,
            ':locked_until' => $lockUntilSql,
            ':id' => $userId,
        ]);

        return $attempts;
    }

    public static function clearFailedLogin(int $userId): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = :id');
        return $statement->execute([':id' => $userId]);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }
}
