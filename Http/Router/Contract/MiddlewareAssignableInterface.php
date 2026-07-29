<?php

declare(strict_types=1);

namespace Atlas\Http\Router\Contract;

interface MiddlewareAssignableInterface
{
    /**
     * Добавление мидлвеера
     *
     * @param  callable|string $middleware коллбек функция или неймспейс класса мидлвеера
     * @return MiddlewareAMiddlewareAssignableInterfacessignable
     */
    public function addMiddleware(callable|string $middleware): MiddlewareAssignableInterface;
}
