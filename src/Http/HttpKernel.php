<?php

declare(strict_types=1);

namespace Atlas\Http;

use Atlas\Common\Contract\ErrorHandlerInterface;
use Atlas\Container\ContainerInterface;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\Http\Contract\HTTPKernelInterface;
use Atlas\Http\Enum\KernelEvent;
use Atlas\Http\Enum\StatusCode;
use Atlas\Http\Exceptions\HttpException;
use Atlas\Http\Exceptions\HttpNotAcceptableException;
use Atlas\Http\Observer\KernelRequestObserver;
use Atlas\Http\Router\Contract\HttpRouterInterface;
use Atlas\Logger\Contract\LoggerInterface;
use Atlas\Http\Contract\ServerResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Ядро обработки HTTP-запросов
 */
final class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private readonly ServerResponseInterface $response,
        private readonly HttpRouterInterface $router,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ContainerInterface $container,
    ) {}

    public function handle(ServerRequestInterface $request): ServerResponseInterface
    {
        try {
            $result = $this->router->dispatch($request);

            $message = $result;
            $statusCode = StatusCode::STATUS_OK->value;
            $responseContentType = 'text/html; charset=utf-8';

            if (is_array($result) === true) {
                $responseContentType = 'application/json';
                $message = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            if ($result instanceof ServerResponseInterface === true) {
                $statusCode = $result->getStatusCode();
                $responseContentType = 'text/html; charset=utf-8';
                $message = $result->getBody();
            }

            $isContentTypeAccepted = $this->isContentTypeAccepted(
                $responseContentType,
                $request->getHeader('Accept'),
            );

            if ($isContentTypeAccepted === false) {
                throw new HttpNotAcceptableException();
            }

            $response = $this->response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', $responseContentType);

            $response->getBody()->write((string) $message);
        } catch (HttpException $e) {
            $this->logger->error($e->getMessage());

            $body = $this->errorHandler->handle($e);

            $response = $this->response
                ->withStatus(
                    $e->getStatusCode(),
                    $e->getMessage(),
                );

            if ($response->hasHeader('Content-Type') === false) {
                $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
            }

            $response->getBody()->write($body);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());

            $body = $this->errorHandler->handle($e);

            $response = $this->response
                ->withStatus(
                    StatusCode::STATUS_INTERNAL_SERVER_ERROR->value,
                    $e->getMessage(),
                );

            if ($response->hasHeader('Content-Type') === false) {
                $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
            }

            $response->getBody()->write($body);

            $observer = $this->container->get(KernelRequestObserver::class);

            $this->eventDispatcher->attach(KernelEvent::REQUEST->value, $observer);
        } finally {
        }

        return $response;
    }

    private function isContentTypeAccepted(?string $contentType, ?array $acceptTypes): bool
    {
        if (empty($acceptTypes) === true) {
            return true;
        }

        $contentTypeBase = trim(explode(';', $contentType)[0]);

        $acceptTypes = explode(',', $acceptTypes[0]);

        foreach ($acceptTypes as $acceptType) {
            $acceptTypeBase = trim(explode(';', $acceptType)[0]);
            $regex = '/^' . str_replace('\*', '.*', preg_quote($acceptTypeBase, '/')) . '$/';

            if (preg_match($regex, $contentTypeBase) === 1) {
                return true;
            }
        }

        return false;
    }
}
