<?php

declare(strict_types=1);

namespace Atlas\Http\Router;

use Atlas\Http\Router\Contract\MiddlewareAssignable;

final class Route implements MiddlewareAssignable
{
    public function __construct(
        public string $method,
        public string $path,
        public array $params = [],
        private string|\Closure $handler,
        private array $middlewares = [],
    ) {}

    public function addMiddleware(callable|string $middleware): MiddlewareAssignable
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getHandler(): callable|string
    {
        return $this->handler;
    }
}
