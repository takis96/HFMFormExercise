<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class View
{
    public function __construct(private readonly string $viewPath)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $file = $this->viewPath . '/' . $template . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$template}");
        }

        // EXTR_SKIP stops view data from overwriting variables used by this renderer.
        extract($data, EXTR_SKIP);
        // Buffer the template so the controller receives one complete response string.
        ob_start();

        try {
            require $file;
            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            // Discard partial HTML before allowing the application to handle the error.
            ob_end_clean();
            throw $exception;
        }
    }
}

