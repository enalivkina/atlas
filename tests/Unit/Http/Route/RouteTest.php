<?php

namespace Atlas\tests\Unit\Http\Route;

use Atlas\Http\Router\Route;
use Atlas\tests\Support\Http\Route\AuthMiddleware;
use Atlas\tests\Support\Http\Route\LogMiddleware;
use Atlas\tests\Support\Http\Route\UserController;
use Codeception\PHPUnit\TestCase;

final class RouteTest extends TestCase
{
    public function testCreatesRoute(): void
    {
        $route = new Route(
            method: 'GET',
            path: '/users/{id}',
            regex: '#^/users/(?<id>\d+)$#',
            handler: [UserController::class, 'show'],
        );

        self::assertSame('GET', $route->method);
        self::assertSame('/users/{id}', $route->path);
        self::assertSame('#^/users/(?<id>\d+)$#', $route->regex);

        self::assertSame(
            [UserController::class, 'show'],
            $route->handler
        );

        self::assertSame([], $route->middlewares);
        self::assertSame([], $route->params);
        self::assertSame([], $route->groupStack);
    }

    public function testCreatesRouteWithAllArguments(): void
    {
        $route = new Route(
            method: 'POST',
            path: '/posts',
            regex: '#^/posts$#',
            handler: [UserController::class, 'store'],
            middlewares: [
                AuthMiddleware::class,
            ],
            params: [
                'id' => 15,
            ],
            groupStack: [
                '/api',
            ],
        );

        self::assertSame(
            [AuthMiddleware::class],
            $route->middlewares
        );

        self::assertSame(
            ['id' => 15],
            $route->params
        );

        self::assertSame(
            ['/api'],
            $route->groupStack
        );
    }

    public function testAddsMiddleware(): void
    {
        $route = new Route(
            method: 'GET',
            path: '/',
            regex: '#^/$#',
            handler: [UserController::class, 'index'],
        );

        $result = $route->addMiddleware(AuthMiddleware::class);

        self::assertSame($route, $result);

        self::assertCount(1, $route->middlewares);

        self::assertSame(
            AuthMiddleware::class,
            $route->middlewares[0]
        );
    }

    public function testAddsMultipleMiddlewares(): void
    {
        $route = new Route(
            method: 'GET',
            path: '/',
            regex: '#^/$#',
            handler: [UserController::class, 'index'],
        );

        $route
            ->addMiddleware(AuthMiddleware::class)
            ->addMiddleware(LogMiddleware::class);

        self::assertSame(
            [
                AuthMiddleware::class,
                LogMiddleware::class,
            ],
            $route->middlewares
        );
    }

    public function testAddsClosureMiddleware(): void
    {
        $route = new Route(
            method: 'GET',
            path: '/',
            regex: '#^/$#',
            handler: [UserController::class, 'index'],
        );

        $middleware = static fn () => true;

        $route->addMiddleware($middleware);

        self::assertSame(
            $middleware,
            $route->middlewares[0]
        );
    }
}