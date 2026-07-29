<?php

declare(strict_types=1);

namespace Atlas\Console;

use Atlas\Common\Contract\ErrorHandlerInterface;
use Atlas\View\ViewInterface;
use Atlas\View\ViewNotFoundException;
use Throwable;

final class ConsoleErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly AnsiDecorator $decorator,
        private readonly ViewInterface $renderer,
    ) {}

    public function handle(Throwable $throwable): string
    {
        $params = [
            'exception' => $throwable,
            'decorator' => $this->decorator,
        ];

        try {
            return $this->renderer->render('error', $params);
        } catch (ViewNotFoundException) {
            return $this->renderer->render('@framework/console/error', $params);
        }
    }

    public function defineMode(string $mode): void
    {
        throw new \BadMethodCallException("Метод defineMode() ещё не реализован.");
    }
}
