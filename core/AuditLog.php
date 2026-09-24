<?php
declare(strict_types=1);

class AuditLog
{
    public static function log(
        string $action,
        string $module = 'system',
        string $description = '',
        ?int $userId = null,
        ?string $username = null,
        ?string $recordType = null,
        ?int $recordId = null,
        ?string $ipAddress = null
    ): void {
        if (!function_exists('get_db')) {
            return;
        }

        try {
            $pdo = get_db();
            $statement = $pdo->prepare(
                'INSERT INTO audit_logs (user_id, username, action, module, record_type, record_id, description, ip_address, created_at)
                 VALUES (:user_id, :username, :action, :module, :record_type, :record_id, :description, :ip_address, NOW())'
            );
            $statement->execute([
                ':user_id' => $userId,
                ':username' => $username,
                ':action' => $action,
                ':module' => $module,
                ':record_type' => $recordType,
                ':record_id' => $recordId,
                ':description' => $description,
                ':ip_address' => $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            ]);
        } catch (Throwable $exception) {
            // Ignore audit-log insertion failures when the DB is not ready.
        }
    }
}
