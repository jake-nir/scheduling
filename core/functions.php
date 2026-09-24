<?php
declare(strict_types=1);

function normalize_permission(string $permission): string
{
    $normalized = trim(strtolower($permission));
    $normalized = str_replace(':', '.', $normalized);
    $normalized = str_replace('_', '.', $normalized);
    return $normalized;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function setting(string $key, mixed $default = null): mixed
{
    return $_SESSION['settings'][$key] ?? $default;
}

function user_has_permission(string $permission): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }

    $permissions = $user['permissions'] ?? [];
    return in_array(normalize_permission($permission), $permissions, true);
}

function app_env(): string
{
    global $config;
    $env = $config['env'] ?? 'production';
    return is_string($env) ? $env : 'production';
}

function render_error_page(int $statusCode): void
{
    http_response_code($statusCode);
    include APP_ROOT . '/views/errors/' . $statusCode . '.php';
    exit;
}

function dispatch_controller(string $controllerName, string $methodName): void
{
    $controllerFile = APP_ROOT . '/controllers/' . $controllerName . '.php';

    if (!is_file($controllerFile)) {
        throw new RuntimeException(sprintf(
            'Controller not found: route "%s" expects "%s" at %s (file does not exist).',
            $GLOBALS['__debug_request']['route'] ?? '',
            $controllerName,
            $controllerFile
        ));
    }

    require_once $controllerFile;

    if (!class_exists($controllerName)) {
        throw new RuntimeException(sprintf(
            'Controller not found: class "%s" was not declared in %s.',
            $controllerName,
            $controllerFile
        ));
    }

    $controller = new $controllerName();
    $GLOBALS['__debug_request']['method_exists'] = method_exists($controller, $methodName);

    if (!$GLOBALS['__debug_request']['method_exists']) {
        throw new RuntimeException(sprintf(
            'Method not found: "%s" does not exist on controller "%s" (route "%s").',
            $methodName,
            $controllerName,
            $GLOBALS['__debug_request']['route'] ?? ''
        ));
    }

    $controller->{$methodName}();
}
