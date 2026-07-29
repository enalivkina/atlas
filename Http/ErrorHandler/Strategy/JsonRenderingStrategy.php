<?php

declare(strict_types=1);

namespace Atlas\Http\ErrorHandler\Strategy;

use Atlas\Container\ContainerInterface;
use Atlas\Http\Contract\ServerResponseInterface;
use Atlas\Http\Exceptions\HttpException;
use Atlas\Logger\DebugTagStorage;
use Throwable;

final readonly class JsonRenderingStrategy implements RenderingStrategyInterface
{
    public function __construct(
        private DebugTagStorage $debugTagStorage,
        private ContainerInterface $container,
    ) {}

    public function execute(Throwable $throwable): string
    {
        $response = $this->container->get(ServerResponseInterface::class);
        $response = $response->withHeader('Content-Type', 'application/json');
        $this->container->registerSingleton(ServerResponseInterface::class, fn(): ServerResponseInterface => $response);

        $message = $throwable instanceof HttpException === true ? $throwable->getMessage() : $throwable->getMessage();

        return json_encode([
            'message' => $message,
            'x-debug-tag' =>  $this->debugTagStorage->getTag(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
