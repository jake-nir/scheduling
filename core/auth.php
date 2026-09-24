<?php
declare(strict_types=1);

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) ($user['id'] ?? 0);
    $_SESSION['user'] = $user;
}

function logout_user(): void
{
    $flash = $_SESSION['flash'] ?? [];
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    session_start();

    if (!empty($flash)) {
        $_SESSION['flash'] = $flash;
    }
}

function is_logged_in(): bool
{
    $user = current_user();
    return !empty($user);
}

function current_user(): ?array
{
    $sessionUser = $_SESSION['user'] ?? null;
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if (!$userId && is_array($sessionUser) && !empty($sessionUser['id'])) {
        $userId = (int) $sessionUser['id'];
    }

    if (!$userId) {
        return null;
    }

    if (is_array($sessionUser) && !empty($sessionUser['id'])) {
        $user = User::findById($userId);
        if ($user === null) {
            logout_user();
            return null;
        }

        $role = Role::findById((int) ($user['role_id'] ?? 0));
        $user['role_name'] = $role['role_name'] ?? ($sessionUser['role_name'] ?? '');
        $user['permissions'] = Role::getPermissionsForRoleId((int) ($user['role_id'] ?? 0));
        $_SESSION['user'] = $user;
        return $user;
    }

    $user = User::findById($userId);
    if ($user === null) {
        logout_user();
        return null;
    }

    if (($user['status'] ?? 'inactive') !== 'active') {
        logout_user();
        return null;
    }

    $role = Role::findById((int) ($user['role_id'] ?? 0));
    $user['role_name'] = $role['role_name'] ?? '';
    $user['permissions'] = Role::getPermissionsForRoleId((int) ($user['role_id'] ?? 0));
    $_SESSION['user'] = $user;
    return $user;
}

function require_permission(string $permission = 'dashboard.view'): void
{
    if (!is_logged_in()) {
        redirect((string) ($_SERVER['APP_PUBLIC_URL'] ?? 'http://localhost/duty-scheduling/public') . '/?route=auth/login');
    }

    $normalizedPermission = normalize_permission($permission);
    $user = current_user();
    $permissions = $user['permissions'] ?? [];

    if (!in_array($normalizedPermission, $permissions, true)) {
        http_response_code(403);
        include APP_ROOT . '/views/errors/403.php';
        exit;
    }
}
