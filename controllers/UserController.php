<?php
declare(strict_types=1);

class UserController
{
    public function index(): void
    {
        require_permission('users.manage');

        $pageTitle = 'User Management';
        $users = User::all();
        $roles = Role::all();
        $viewFile = APP_ROOT . '/views/users/list.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function create(): void
    {
        require_permission('users.manage');

        $pageTitle = 'Create User';
        $user = null;
        $roles = Role::all();
        $viewFile = APP_ROOT . '/views/users/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function edit(): void
    {
        require_permission('users.manage');

        $id = (int) ($_GET['id'] ?? 0);
        $user = User::findById($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(APP_PUBLIC_URL . '/?route=users/index');
        }

        $pageTitle = 'Edit User';
        $roles = Role::all();
        $viewFile = APP_ROOT . '/views/users/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function save(): void
    {
        require_permission('users.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=users/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=users/index');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $username = trim((string) ($_POST['username'] ?? ''));
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $fullName === '' || $roleId <= 0) {
            flash('error', 'Please complete the required user fields.');
            redirect(APP_PUBLIC_URL . '/?route=users/create');
        }

        $data = [
            'username' => $username,
            'full_name' => $fullName,
            'email' => $email,
            'role_id' => $roleId,
            'status' => $status,
        ];

        if ($password !== '') {
            $data['password'] = $password;
        }

        if ($id > 0) {
            $existing = User::findById($id);
            if (!$existing) {
                flash('error', 'User not found.');
                redirect(APP_PUBLIC_URL . '/?route=users/index');
            }

            User::update($id, $data);
            flash('success', 'User updated successfully.');
        } else {
            if ($password === '') {
                flash('error', 'A password is required for a new user.');
                redirect(APP_PUBLIC_URL . '/?route=users/create');
            }

            $data['password'] = $password;
            User::create($data);
            flash('success', 'User created successfully.');
        }

        AuditLog::log(
            $id > 0 ? 'user.update' : 'user.create',
            'users',
            $id > 0 ? 'User updated.' : 'User created.',
            current_user()['id'] ?? null,
            current_user()['username'] ?? null,
            'users',
            $id > 0 ? $id : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        redirect(APP_PUBLIC_URL . '/?route=users/index');
    }

    public function deactivate(): void
    {
        require_permission('users.manage');

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirect(APP_PUBLIC_URL . '/?route=users/index');
        }

        $user = User::findById($id);
        if ($user) {
            User::setStatus($id, 'inactive');
            AuditLog::log('user.deactivate', 'users', 'User deactivated.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'users', $id, $_SERVER['REMOTE_ADDR'] ?? null);
            flash('success', 'User deactivated.');
        }

        redirect(APP_PUBLIC_URL . '/?route=users/index');
    }

    public function resetPassword(): void
    {
        require_permission('users.manage');

        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $newPassword = trim((string) ($_POST['new_password'] ?? ''));

        if ($id <= 0) {
            redirect(APP_PUBLIC_URL . '/?route=users/index');
        }

        if ($newPassword === '') {
            $newPassword = 'admin123';
        }

        User::setPassword($id, $newPassword);
        flash('success', 'Password reset successfully.');
        AuditLog::log('user.password_reset', 'users', 'User password reset.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'users', $id, $_SERVER['REMOTE_ADDR'] ?? null);

        redirect(APP_PUBLIC_URL . '/?route=users/index');
    }

    public function roles(): void
    {
        require_permission('users.manage');

        $pageTitle = 'Role Management';
        $roles = Role::all();
        $viewFile = APP_ROOT . '/views/users/roles.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function roleCreate(): void
    {
        require_permission('users.manage');

        $pageTitle = 'Create Role';
        $role = null;
        $permissions = Role::PERMISSION_KEYS;
        $viewFile = APP_ROOT . '/views/users/role-form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function roleEdit(): void
    {
        require_permission('users.manage');

        $id = (int) ($_GET['id'] ?? 0);
        $role = Role::findById($id);
        if (!$role) {
            flash('error', 'Role not found.');
            redirect(APP_PUBLIC_URL . '/?route=roles/index');
        }

        $pageTitle = 'Edit Role';
        $permissions = Role::PERMISSION_KEYS;
        $viewFile = APP_ROOT . '/views/users/role-form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function saveRole(): void
    {
        require_permission('users.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=roles/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=roles/index');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $roleName = trim((string) ($_POST['role_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $selectedPermissions = array_filter(array_keys($_POST), static function (string $key): bool {
            return str_starts_with($key, 'perm_');
        });

        $permissions = [];
        foreach ($selectedPermissions as $key) {
            $permission = str_replace('perm_', '', $key);
            $permissions[] = normalize_permission($permission);
        }

        if ($roleName === '') {
            flash('error', 'Role name is required.');
            redirect(APP_PUBLIC_URL . '/?route=roles/create');
        }

        $payload = [
            'role_name' => $roleName,
            'description' => $description,
            'permissions' => $permissions,
            'is_active' => 1,
        ];

        if ($id > 0) {
            Role::update($id, $payload);
            flash('success', 'Role updated successfully.');
        } else {
            Role::create($payload);
            flash('success', 'Role created successfully.');
        }

        redirect(APP_PUBLIC_URL . '/?route=roles/index');
    }
}
