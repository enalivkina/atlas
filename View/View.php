<?php

declare(strict_types=1);

namespace Atlas\View;

use Atlas\Common\AliasManager;

final class View implements ViewInterface
{
    public function __construct(
        private readonly AliasManager $aliasManager,
        string $rootPath,
    ) {
        $this->aliasManager->addAlias('@view', $rootPath);
    }

    public function render(string $view, array $params = []): string
    {
        if ($this->aliasManager->hasAlias($view) === false) {
            $view = '@view/' . $view;
        }

        $filePath = $this->aliasManager->buildPath($view) . '.php';

        if (file_exists($filePath) === false) {
            throw new ViewNotFoundException($filePath);
        }

        extract($params);

        ob_start();

        include $filePath;

        return ob_get_clean();
    }
}
