<?php
declare(strict_types=1);

$flashMessages = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Duty Scheduling</title>
    <link rel="stylesheet" href="<?= e(APP_PUBLIC_URL . '/assets/css/app.css'); ?>">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="brand-wrap">
            <div class="brand-mark">D</div>
            <div>
                <div class="brand-name">Duty Scheduling</div>
                <div class="brand-subtitle">Operations &amp; Planning</div>
            </div>
        </div>

        <?php foreach ($flashMessages as $flash): ?>
            <div class="alert alert-<?= e($flash['type']); ?>"><?= e($flash['message']); ?></div>
        <?php endforeach; ?>

        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=auth/login'); ?>" class="login-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" autocomplete="username" required value="admin">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div class="helper-text">
            Temporary test account: <strong>admin</strong> / <strong>admin123</strong>
        </div>
    </div>
</body>
</html>
