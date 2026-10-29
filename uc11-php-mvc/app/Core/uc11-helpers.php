<?php
declare(strict_types=1);
/** Escape dữ liệu trước khi xuất vào HTML. */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
