<?php

declare(strict_types=1);

namespace Atlas\tests\Unit\Http;

use Atlas\Common\Contract\ErrorHandlerInterface;
use Atlas\Container\ContainerInterface;
use Atlas\Http\Contract\ServerResponseInterface;
use Atlas\Http\Exceptions\HttpException;
use Atlas\Http\Exceptions\HttpNotAcceptableException;
use Atlas\Http\HttpKernel;
use Atlas\Http\Response;
use Atlas\Http\Router\Contract\HttpRouterInterface;
use Atlas\Logger\Contract\LoggerInterface;
use Atlas\tests\Support\Http\TestModule;
use Codeception\PHPUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

final class HttpKernelTest extends TestCase
{
    private ServerResponseInterface&MockObject $response;
    private HttpRouterInterface&MockObject $router;
    private LoggerInterface&MockObject $logger;
    private ErrorHandlerInterface&MockObject $errorHandler;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->response = $this->createMock(ServerResponseInterface::class);
        $this->router = $this->createMock(HttpRouterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->errorHandler = $this->createMock(ErrorHandlerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
    }

    private function createKernel(): HttpKernel
    {
        return new HttpKernel(
            router: $this->router,
            logger: $this->logger,
            errorHandler: $this->errorHandler,
            container: $this->container,
        );
    }

    public function testShouldReturnHtmlResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $request
            ->method('getHeader')
            ->with('Accept')
            ->willReturn(['text/html']);

        $this->router
            ->expects(self::once())
            ->method('dispatch')
            ->with($request)
            ->willReturn('Hello');

        $this->response
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'text/html; charset=utf-8')
            ->willReturnSelf();

        $this->response
            ->method('getBody')
            ->willReturn($body);

        $this->container->method('get')
            ->with(ServerResponseInterface::class)
            ->willReturn($this->response);

        $body
            ->expects(self::once())
            ->method('write')
            ->with('Hello');

        $kernel = $this->createKernel();

        self::assertSame($this->response, $kernel->handle($request));
    }

    public function testShouldReturnJsonResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $request
            ->method('getHeader')
            ->willReturn(['application/json']);

        $this->router
            ->method('dispatch')
            ->willReturn([
                'success' => true,
            ]);

        $this->response
            ->method('withStatus')
            ->willReturnSelf();

        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();

        $this->response
            ->method('getBody')
            ->willReturn($body);

        $this->container->method('get')
            ->with(ServerResponseInterface::class)
            ->willReturn($this->response);

        $body
            ->expects(self::once())
            ->method('write')
            ->with('{"success":true}');

        $kernel = $this->createKernel();

        $kernel->handle($request);
    }

    public function testShouldReturnCustomResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $custom = $this->createMock(Response::class);
        $customBody = $this->createMock(StreamInterface::class);

        $custom
            ->method('getStatusCode')
            ->willReturn(201);

        $custom
            ->method('getBody')
            ->willReturn($customBody);

        $customBody
            ->method('getContents')
            ->willReturn('created');

        $body = $this->createMock(StreamInterface::class);

        $request
            ->method('getHeader')
            ->willReturn(['text/html']);

        $this->router
            ->method('dispatch')
            ->willReturn($custom);

        $this->response
            ->method('withStatus')
            ->with(500)
            ->willReturnSelf();

        $this->response
            ->method('withHeader')
            ->willReturnSelf();

        $this->response
            ->method('getBody')
            ->willReturn($body);

        $this->container->method('get')
            ->with(ServerResponseInterface::class)
            ->willReturn($this->response);

        $kernel = $this->createKernel();

        $kernel->handle($request);
    }

    public function testShouldHandleHttpException(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $exception = $this->createMock(HttpException::class);

        $exception
            ->method('getStatusCode')
            ->willReturn(404);

        $this->router
            ->method('dispatch')
            ->willThrowException($exception);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($exception);

        $this->errorHandler
            ->expects(self::once())
            ->method('handle')
            ->with($exception)
            ->willReturn('404 page');

        $this->response
            ->method('withStatus')
            ->willReturnSelf();

        $this->response
            ->method('hasHeader')
            ->willReturn(false);

        $this->response
            ->method('withHeader')
            ->willReturnSelf();

        $this->response
            ->method('getBody')
            ->willReturn($body);

        $this->container->method('get')
            ->with(ServerResponseInterface::class)
            ->willReturn($this->response);

        $body
            ->expects(self::once())
            ->method('write')
            ->with('404 page');

        $kernel = $this->createKernel();

        $kernel->handle($request);
    }

    public function testShouldHandleThrowable(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $exception = new \RuntimeException('Boom');

        $this->router
            ->method('dispatch')
            ->willThrowException($exception);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($exception);

        $this->errorHandler
            ->expects(self::once())
            ->method('handle')
            ->with($exception)
            ->willReturn('500');

        $this->response
            ->method('withStatus')
            ->willReturnSelf();

        $this->response
            ->method('hasHeader')
            ->willReturn(false);

        $this->response
            ->method('withHeader')
            ->willReturnSelf();

        $this->response
            ->method('getBody')
            ->willReturn($body);

        $this->container->method('get')
            ->with(ServerResponseInterface::class)
            ->willReturn($this->response);

        $body
            ->expects(self::once())
            ->method('write')
            ->with('500');

        $kernel = $this->createKernel();

        $kernel->handle($request);
    }

    public function testShouldThrowNotAcceptable(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $request
            ->method('getHeader')
            ->willReturn(['application/xml']);

        $this->router
            ->method('dispatch')
            ->willReturn('Hello');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(self::isInstanceOf(HttpNotAcceptableException::class));

        $this->errorHandler
            ->expects(self::once())
            ->method('handle')
            ->willReturn('406');

        $this->response
            ->method('withStatus')
            ->willReturnSelf();

        $this->response
            ->method('hasHeader')
            ->willReturn(false);

        $this->response
            ->method('withHeader')
            ->willReturnSelf();

        $this->response
            ->method('getBody')
            ->willReturn($body);

        $this->container->method('get')
            ->with(ServerResponseInterface::class)
            ->willReturn($this->response);

        $body
            ->expects(self::once())
            ->method('write')
            ->with('406');

        $kernel = $this->createKernel();

        $kernel->handle($request);
    }

    public function testShouldInitializeModules(): void
    {
        $this->container
            ->expects(self::once())
            ->method('call')
            ->with(TestModule::class, 'init');

        new HttpKernel(
            router: $this->router,
            logger: $this->logger,
            errorHandler: $this->errorHandler,
            container: $this->container,
            modules: [
                TestModule::class,
            ],
        );
    }

    public function testShouldThrowWhenModuleDoesNotImplementInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new HttpKernel(
            router: $this->router,
            logger: $this->logger,
            errorHandler: $this->errorHandler,
            container: $this->container,
            modules: [
                \stdClass::class,
            ],
        );
    }
}
