<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /** @param array<string, mixed> $data */
    protected function view(string $template, array $data = []): void
    {
        View::render($template, $data);
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}

