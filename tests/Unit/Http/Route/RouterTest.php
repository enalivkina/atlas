<?php

declare(strict_types=1);

namespace Atlas\tests\Unit\Http\Route;

use Atlas\Container\ContainerInterface;
use Atlas\Http\Router\Route;
use Atlas\Http\Router\Router;
use Atlas\tests\Support\Http\Route\AuthMiddleware;
use Atlas\tests\Support\Http\Route\TestController;
use Atlas\Validator\Contract\ValidatorInterface;
use Codeception\PHPUnit\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $this->router = new Router($container, $validator);
    }

    public function testRegistersGetRoute(): void
    {
        $route = $this->router->get(
            '/users',
            [TestController::class, 'index']
        );

        self::assertInstanceOf(Route::class, $route);

        self::assertSame('GET', $route->method);
        self::assertSame('/users', $route->path);

        self::assertTrue(
            $this->router->has('GET', '/users')
        );
    }

    public function testRegistersPostRoute(): void
    {
        $this->router->post(
            '/users',
            [TestController::class, 'store']
        );

        self::assertTrue(
            $this->router->has('POST', '/users')
        );
    }

    public function testRegistersPutRoute(): void
    {
        $this->router->put(
            '/users',
            [TestController::class, 'update']
        );

        self::assertTrue(
            $this->router->has('PUT', '/users')
        );
    }

    public function testRegistersPatchRoute(): void
    {
        $this->router->patch(
            '/users',
            [TestController::class, 'patch']
        );

        self::assertTrue(
            $this->router->has('PATCH', '/users')
        );
    }

    public function testRegistersDeleteRoute(): void
    {
        $this->router->delete(
            '/users',
            [TestController::class, 'delete']
        );

        self::assertTrue(
            $this->router->has('DELETE', '/users')
        );
    }

    public function testHasReturnsFalseForUnknownRoute(): void
    {
        self::assertFalse(
            $this->router->has('GET', '/unknown')
        );
    }

    public function testHasIsCaseInsensitiveForMethod(): void
    {
        $this->router->get(
            '/users',
            [TestController::class, 'index']
        );

        self::assertTrue(
            $this->router->has('get', '/users')
        );
    }

    public function testMatchesDynamicRoute(): void
    {
        $this->router->get(
            '/users/{:id}',
            [TestController::class, 'show']
        );

        self::assertTrue(
            $this->router->has('GET', '/users/15')
        );

        self::assertTrue(
            $this->router->has('GET', '/users/999')
        );
    }

    public function testCreatesRegexForDynamicRoute(): void
    {
        $route = $this->router->get(
            '/users/{:id}',
            [TestController::class, 'show']
        );

        self::assertMatchesRegularExpression(
            $route->regex,
            '/users/42'
        );
    }

    public function testParsesRequiredParameters(): void
    {
        $route = $this->router->get(
            '/users/{:id|int}',
            [TestController::class, 'show']
        );

        self::assertSame(
            [
                [
                    'name' => 'id',
                    'type' => 'int',
                    'required' => true,
                    'default' => null,
                ],
            ],
            $route->params
        );
    }

    public function testParsesOptionalParameters(): void
    {
        $route = $this->router->get(
            '/users/{?:page|int=1}',
            [TestController::class, 'show']
        );

        self::assertSame(
            [
                [
                    'name' => 'page',
                    'type' => 'int',
                    'required' => false,
                    'default' => '1',
                ],
            ],
            $route->params
        );
    }

    public function testAddsGlobalMiddleware(): void
    {
        $this->router->addMiddleware(AuthMiddleware::class);

        $route = $this->router->get(
            '/users',
            [TestController::class, 'index']
        );

        self::assertSame(
            [AuthMiddleware::class],
            $route->middlewares
        );
    }

    public function testCreatesGroupPrefix(): void
    {
        $this->router->group(
            'api',
            function (Router $router): void {
                $router->get(
                    '/users',
                    [TestController::class, 'index']
                );
            }
        );

        self::assertTrue(
            $this->router->has(
                'GET',
                '/api/users'
            )
        );
    }

    public function testNestedGroups(): void
    {
        $this->router->group(
            'api',
            function (Router $router): void {

                $router->group(
                    'v1',
                    function (Router $router): void {

                        $router->get(
                            '/users',
                            [TestController::class, 'index']
                        );
                    }
                );
            }
        );

        self::assertTrue(
            $this->router->has(
                'GET',
                '/api/v1/users'
            )
        );
    }
}