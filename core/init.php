<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost/duty-scheduling');
}

if (!defined('APP_PUBLIC_URL')) {
    define('APP_PUBLIC_URL', APP_URL . '/public');
}

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';

global $config, $dbConfig;
$dbConfig = $dbConfig ?? [];

$timezone = $config['timezone'] ?? 'UTC';
date_default_timezone_set($timezone);
error_reporting(E_ALL);
ini_set('display_errors', ($config['env'] ?? 'production') === 'local' ? '1' : '0');
ini_set('log_errors', '1');

session_name($config['session_name'] ?? 'duty_sched_session');
$secureCookie = (isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

global $_APP_CONFIG;
$_APP_CONFIG = $config;

$_SERVER['APP_PUBLIC_URL'] = APP_PUBLIC_URL;

spl_autoload_register(static function (string $class): void {
    $candidateFiles = [
        APP_ROOT . '/core/' . $class . '.php',
        APP_ROOT . '/controllers/' . $class . '.php',
        APP_ROOT . '/models/' . $class . '.php',
        APP_ROOT . '/services/' . $class . '.php',
    ];

    foreach ($candidateFiles as $candidateFile) {
        if (is_file($candidateFile)) {
            require_once $candidateFile;
            return;
        }
    }
});

require_once APP_ROOT . '/core/functions.php';
require_once APP_ROOT . '/core/db.php';
require_once APP_ROOT . '/core/auth.php';
require_once APP_ROOT . '/core/csrf.php';
require_once APP_ROOT . '/core/router.php';
require_once APP_ROOT . '/core/validation.php';
require_once APP_ROOT . '/core/AuditLog.php';

require_once APP_ROOT . '/controllers/AuthController.php';
require_once APP_ROOT . '/controllers/DashboardController.php';
