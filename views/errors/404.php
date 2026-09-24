<?php
declare(strict_types=1);

$isDev = app_env() === 'local';
$requestedRoute = $route ?? ($_GET['route'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page not found</title>
    <link rel="stylesheet" href="<?= e(APP_PUBLIC_URL . '/assets/css/app.css'); ?>">
</head>
<body class="auth-page">
    <div class="auth-card error-card">
        <div class="brand-wrap">
            <div class="brand-mark">D</div>
            <div>
                <div class="brand-name">Duty Scheduling</div>
                <div class="brand-subtitle">System</div>
            </div>
        </div>
        <h1>404</h1>
        <p>The page you requested could not be found.</p>
        <?php if ($isDev): ?>
            <div class="debug-box" style="margin:1rem 0;padding:.75rem;background:#fff3cd;border:1px solid #ffc107;border-radius:6px;text-align:left;font-size:.85rem;overflow:auto;">
                <strong>Requested route:</strong> <code><?= e($requestedRoute !== '' ? $requestedRoute : '(none)'); ?></code><br>
                <span>Reason: this route is not defined in <code>core/router.php</code> <code>route_map()</code>. This is a genuine "route does not exist" 404.</span>
            </div>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=auth/login'); ?>">Return to login</a>
    </div>
</body>
</html>
