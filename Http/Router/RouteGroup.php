<?php

declare(strict_types=1);

namespace Atlas\Http\Router;

use Atlas\Http\Router\Contract\MiddlewareAssignable;

final class RouteGroup implements MiddlewareAssignable
{
    private array $middlewares = [];
    private array $routes = [];
    private array $groups = [];

    public function __construct(private readonly string $name) {}

    public function addMiddleware(callable|string $middleware): MiddlewareAssignable
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function addGroup(RouteGroup $group): void
    {
        $this->groups[] = $group;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
