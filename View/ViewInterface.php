<?php

declare(strict_types=1);

namespace Atlas\View;

interface ViewInterface
{
    /**
     * @param string $view
     * @param array $params
     * @return string
     * @throws ViewNotFoundException
     */
    public function render(string $view, array $params = []): string;
}
