<?php

declare(strict_types=1);

namespace Atlas\Http\Router;

use Atlas\Http\Router\Contract\MiddlewareAssignable;

final class Route implements MiddlewareAssignable
{
    public function __construct(
        public string $method,
        public string $path,
        public string $regex,
        public array $handler,
        public array $middlewares = [],
        public array $params = [],
        public array $groupStack = [],
    ) {}

    public function addMiddleware(callable|string $middleware): MiddlewareAssignable
    {
        $this->middlewares[] = $middleware;

        return $this;
    }
}
