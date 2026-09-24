<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_verify(?string $token = null): bool
{
    $submitted = $token ?? ($_POST['csrf_token'] ?? null);
    $expected = $_SESSION['csrf_token'] ?? null;

    if (!is_string($submitted) || !is_string($expected)) {
        return false;
    }

    return hash_equals($expected, $submitted);
}
