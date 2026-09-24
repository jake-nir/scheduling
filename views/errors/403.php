<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Forbidden</title>
    <link rel="stylesheet" href="<?= e(APP_PUBLIC_URL . '/assets/css/app.css'); ?>">
</head>
<body class="auth-page">
    <div class="auth-card error-card">
        <div class="brand-wrap">
            <div class="brand-mark">D</div>
            <div>
                <div class="brand-name">Duty Scheduling</div>
                <div class="brand-subtitle">Access denied</div>
            </div>
        </div>
        <h1>403</h1>
        <p>You do not have permission to access this page.</p>
        <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=dashboard/index'); ?>">Back to dashboard</a>
    </div>
</body>
</html>
