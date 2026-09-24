<?php
declare(strict_types=1);

function ensure_user_security_columns(): void
{
    try {
        $pdo = get_db();
        $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('failed_login_attempts', $columns, true)) {
            $pdo->exec('ALTER TABLE users ADD COLUMN failed_login_attempts INT NOT NULL DEFAULT 0 AFTER status');
        }
        if (!in_array('locked_until', $columns, true)) {
            $pdo->exec('ALTER TABLE users ADD COLUMN locked_until DATETIME NULL AFTER failed_login_attempts');
        }
    } catch (Throwable $exception) {
        // Ignore migration failures during startup; they are retried on the next request.
    }
}

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = $GLOBALS['dbConfig'] ?? [];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'] ?? '127.0.0.1',
        $config['port'] ?? 3306,
        $config['database'] ?? 'duty_scheduling',
        $config['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO(
        $dsn,
        $config['username'] ?? 'root',
        $config['password'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    ensure_user_security_columns();

    return $pdo;
}
