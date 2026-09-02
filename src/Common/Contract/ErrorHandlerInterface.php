<?php

declare(strict_types=1);

namespace Atlas\Common\Contract;

use Throwable;

interface ErrorHandlerInterface
{
    /**
     * @param Throwable $throwable объект ошибки
     * @return string
     */
    public function handle(Throwable $throwable): string;

    public function setMode(string $mode): void;
}
