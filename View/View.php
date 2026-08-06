<?php

declare(strict_types=1);

namespace Atlas\View;

final class View implements ViewInterface
{
    public function __construct(
        private string $basePath = '...',
    ) {
        $this->setBasePath($basePath);
    }

    public function setBasePath(string $path): void
    {
        $this->basePath = rtrim($this->basePath, '/\\');

        if (is_dir($this->basePath) === false) {
            throw new \RuntimeException("Каталог представлений не найден: {$this->basePath}");
        }
    }

    public function render(string $view, array $params = []): string
    {
        $path = $this->basePath . '/' . ltrim($view, '/\\') . '.php';

        if (file_exists($path) === false) {
            throw new ViewNotFoundException($view);
        }

        extract($params);

        ob_start();

        include $filePath;

        return ob_get_clean();
    }
}
