<?php

declare(strict_types=1);

namespace Atlas\Console;

use Atlas\Common\Contract\ErrorHandlerInterface;
use Atlas\Container\ContainerInterface;
use Atlas\Http\Enum\ContentType;
use Atlas\Logger\Contract\DebugTagStorageInterface;
use Atlas\View\ViewInterface;
use Atlas\View\ViewNotFoundException;
use Throwable;

final class ConsoleErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly ViewInterface $view,
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly ContainerInterface $container,
        private string $mode = ContentType::HTML->value,
    ) {}

    public function handle(Throwable $throwable): string
    {
        $params = [
            'exception' => $throwable,
        ];

        try {
            return $this->view->render('error', $params);
        } catch (ViewNotFoundException) {
            return $this->view->render('Views/index', $params);
        }
    }

    public function setMode(string $mode): void
    {
        throw new \BadMethodCallException("Метод setMode() ещё не реализован.");
    }
}
