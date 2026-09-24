<?php
declare(strict_types=1);

$isDev = app_env() === 'local';
$exception = $exception ?? ($GLOBALS['__render_exception'] ?? null);
$debug = $GLOBALS['__debug_request'] ?? [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server error</title>
    <link rel="stylesheet" href="<?= e(APP_PUBLIC_URL . '/assets/css/app.css'); ?>">
</head>
<body class="auth-page">
    <div class="auth-card error-card error-card-wide">
        <div class="brand-wrap">
            <div class="brand-mark">D</div>
            <div>
                <div class="brand-name">Duty Scheduling</div>
                <div class="brand-subtitle">System</div>
            </div>
        </div>
        <h1>500</h1>
        <p>An unexpected error occurred while processing your request. Please try again later.</p>
        <?php if ($isDev): ?>
            <div class="debug-box" style="margin:1rem 0;padding:.75rem;background:#fff3cd;border:1px solid #ffc107;border-radius:6px;text-align:left;font-size:.85rem;overflow:auto;">
                <?php if (!empty($debug['route'])): ?>
                    <div><strong>Requested route:</strong> <code><?= e((string) ($debug['route'] ?? '')); ?></code></div>
                    <div><strong>Controller:</strong> <code><?= e((string) ($debug['controller'] ?? '')); ?></code></div>
                    <div><strong>Method:</strong> <code><?= e((string) ($debug['method'] ?? '')); ?></code></div>
                    <?php if (!empty($debug['controller_file'])): ?>
                        <div><strong>Controller file:</strong> <code><?= e((string) $debug['controller_file']); ?></code></div>
                    <?php endif; ?>
                    <?php if (array_key_exists('controller_exists', $debug)): ?>
                        <div><strong>Controller exists:</strong> <?= $debug['controller_exists'] ? 'YES' : 'NO'; ?></div>
                    <?php endif; ?>
                    <?php if (array_key_exists('method_exists', $debug)): ?>
                        <div><strong>Method exists:</strong> <?= $debug['method_exists'] ? 'YES' : 'NO'; ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($exception instanceof Throwable): ?>
                    <div style="margin-top:.5rem;"><strong><?= e(get_class($exception)); ?></strong>: <?= e($exception->getMessage()); ?></div>
                    <div><code><?= e($exception->getFile()); ?>:<?= e((string) $exception->getLine()); ?></code></div>
                    <pre style="white-space:pre-wrap;max-height:300px;overflow:auto;background:#2b2b2b;color:#e6e6e6;padding:.6rem;border-radius:4px;font-size:.75rem;"><?= e($exception->getTraceAsString()); ?></pre>
                <?php else: ?>
                    <div style="margin-top:.5rem;">No exception object available.</div>
                <?php endif; ?>
                <p style="margin-top:.5rem;margin-bottom:0;">This is a development-only report. It will not be rendered in production.</p>
            </div>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=auth/login'); ?>">Return to login</a>
    </div>
</body>
</html>