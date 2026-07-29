<?php

declare(strict_types=1);

namespace Atlas\Http\Router;

use Atlas\Http\Router\Contract\MiddlewareAssignableInterface;

final class Route implements MiddlewareAssignableInterface
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

    public function addMiddleware(callable|string $middleware): MiddlewareAssignableInterface
    {
        $this->middlewares[] = $middleware;

        return $this;
    }
}
