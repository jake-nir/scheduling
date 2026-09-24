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
