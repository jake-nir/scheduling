<?php
declare(strict_types=1);

class AuthController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'Security token expired. Please try again.');
            } else {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');

                if (!validate_required($username) || !validate_required($password)) {
                    flash('error', 'Username and password are required.');
                } else {
                    $user = User::findByUsername($username);

                    if (!$user || ($user['status'] ?? 'inactive') !== 'active' || !password_verify($password, $user['password_hash'] ?? '')) {
                        flash('error', 'Invalid username or password.');
                    } else {
                        $role = Role::findById((int) ($user['role_id'] ?? 0));
                        $user['role_name'] = $role['role_name'] ?? '';
                        $user['permissions'] = Role::getPermissionsForRoleId((int) ($user['role_id'] ?? 0));

                        login_user($user);
                        User::updateLastLogin((int) $user['id']);
                        AuditLog::log('login', 'auth', 'User logged in.', (int) $user['id'], (string) $user['username'], 'users', (int) $user['id'], $_SERVER['REMOTE_ADDR'] ?? null);
                        redirect(APP_PUBLIC_URL . '/?route=dashboard/index');
                    }
                }
            }
        }

        if (is_logged_in()) {
            redirect(APP_PUBLIC_URL . '/?route=dashboard/index');
        }

        include APP_ROOT . '/views/auth/login.php';
    }

    public function logout(): void
    {
        $user = current_user();
        if ($user) {
            AuditLog::log('logout', 'auth', 'User logged out.', (int) ($user['id'] ?? 0), (string) ($user['username'] ?? ''), 'users', (int) ($user['id'] ?? 0), $_SERVER['REMOTE_ADDR'] ?? null);
        }

        flash('success', 'You have been logged out.');
        logout_user();
        redirect(APP_PUBLIC_URL . '/?route=auth/login');
    }
}
