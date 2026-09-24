<?php
declare(strict_types=1);

$user = $user ?? current_user();
$pageTitle = $pageTitle ?? 'Dashboard';
$viewFile = $viewFile ?? APP_ROOT . '/views/dashboard/index.php';
$flashMessages = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle); ?> - Duty Scheduling</title>
    <link rel="stylesheet" href="<?= e(APP_PUBLIC_URL . '/assets/css/app.css'); ?>">
    <link rel="stylesheet" href="<?= e(APP_PUBLIC_URL . '/assets/css/print.css'); ?>">
</head>
<body class="app-body">
    <div class="app-shell">
        <?php include APP_ROOT . '/views/layouts/sidebar.php'; ?>

        <main class="main-panel">
            <header class="topbar no-print">
                <div>
                    <h1><?= e($pageTitle); ?></h1>
                </div>
                <div class="topbar-actions">
                    <span class="user-pill"><?= e($user['full_name'] ?? 'Administrator'); ?></span>
                    <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=auth/logout'); ?>">Logout</a>
                </div>
            </header>

            <section class="content-area">
                <?php foreach ($flashMessages as $flash): ?>
                    <div class="alert alert-<?= e($flash['type']); ?>"><?= e($flash['message']); ?></div>
                <?php endforeach; ?>

                <?php include $viewFile; ?>
            </section>
        </main>
    </div>

    <script src="<?= e(APP_PUBLIC_URL . '/assets/js/app.js'); ?>"></script>
    <script src="<?= e(APP_PUBLIC_URL . '/assets/js/pod.js'); ?>"></script>
</body>
</html>
