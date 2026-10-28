<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $template, array $data = []): void
    {
        $path = dirname(__DIR__) . '/Views/' . $template . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('Không tìm thấy giao diện: ' . $template);
        }

        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/layouts/header.php';
        require $path;
        require dirname(__DIR__) . '/Views/layouts/footer.php';
    }
}

