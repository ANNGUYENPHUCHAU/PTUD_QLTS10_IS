<?php
declare(strict_types=1);

function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(24)); }
    return $_SESSION['csrf'];
}
function hiddenCsrf(): void {
    echo '<input type="hidden" name="csrf" value="' . h(csrf()) . '">';
}
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function backUC14(): never {
    header('Location: uc14.php');
    exit;
}
