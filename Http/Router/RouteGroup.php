<?php

declare(strict_types=1);

namespace Atlas\Http\Router;

use Atlas\Http\Router\Contract\MiddlewareAssignableInterface;

final class RouteGroup implements MiddlewareAssignableInterface
{
    private array $middlewares = [];

    public function __construct(private readonly string $name) {}

    public function addMiddleware(callable|string $middleware): MiddlewareAssignableInterface
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
