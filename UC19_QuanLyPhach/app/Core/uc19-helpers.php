<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function formFields(string $action): void
{
    echo '<input type="hidden" name="csrf" value="' . e(csrf()) . '">';
    echo '<input type="hidden" name="action" value="' . e($action) . '">';
}

function uc19Url(array $query = []): string
{
    return 'uc19.php' . ($query ? '?' . http_build_query($query) : '');
}
