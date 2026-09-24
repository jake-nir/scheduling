<?php
declare(strict_types=1);

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

    return $pdo;
}
