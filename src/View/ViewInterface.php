<?php

declare(strict_types=1);

namespace Atlas\View;

interface ViewInterface
{
    public function setBasePath(string $path): void;
    /**
     * @param string $view
     * @param array $params
     * @return string
     * @throws ViewNotFoundException
     */
    public function render(string $view, array $params = []): string;
}
