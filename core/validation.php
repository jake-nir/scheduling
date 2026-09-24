<?php
declare(strict_types=1);

function validate_required(mixed $value): bool
{
    if (is_string($value)) {
        return trim($value) !== '';
    }

    if (is_array($value)) {
        return count($value) > 0;
    }

    return $value !== null && $value !== '';
}

function validate_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
    return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
}

function validate_numeric(mixed $value): bool
{
    return is_numeric((string) $value);
}

function validate_enum(mixed $value, array $allowed): bool
{
    return in_array((string) $value, $allowed, true);
}
