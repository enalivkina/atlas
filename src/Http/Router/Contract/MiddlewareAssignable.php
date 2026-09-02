<?php

declare(strict_types=1);

namespace Atlas\Http\Router\Contract;

interface MiddlewareAssignable
{
    /**
     * Добавление мидлвеера
     *
     * @param  callable|string $middleware коллбек функция или неймспейс класса мидлвеера
     * @return MiddlewareAssignable
     */
    public function addMiddleware(callable|string $middleware): MiddlewareAssignable;
}
