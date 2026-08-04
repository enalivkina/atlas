<?php

declare(strict_types=1);

namespace Atlas\Http\ErrorHandler;

use Atlas\Common\Contract\ErrorHandlerInterface;
use Atlas\Container\ContainerInterface;
use Atlas\Http\Enum\ContentType;
use Atlas\Http\ErrorHandler\Strategy\HtmlRenderingStrategy;
use Atlas\Http\ErrorHandler\Strategy\JsonRenderingStrategy;
use Atlas\Http\ErrorHandler\Strategy\StrategyNotFoundException;
use Atlas\Logger\Contract\DebugTagStorageInterface;
use Atlas\View\ViewInterface;
use Atlas\View\ViewNotFoundException;

final class HttpErrorHandler implements ErrorHandlerInterface
{
    private array $renderingStrategies = [
        ContentType::HTML->value => HtmlRenderingStrategy::class,
        ContentType::JSON->value => JsonRenderingStrategy::class,
    ];

    public function __construct(
        private readonly ViewInterface $view,
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly ContainerInterface $container,
        private string $mode = ContentType::HTML->value,
    ) { }

    /**
     * @throws ViewNotFoundException
     * @throws StrategyNotFoundException
     */
    public function handle(\Throwable $throwable): string
    {
        try {
            if (isset($this->renderingStrategies[$this->mode]) === false) {
                throw new StrategyNotFoundException("Стратегия для режима {$this->mode} не найдена");
            }

            return $this->container->call(
                $this->renderingStrategies[$this->mode],
                'execute',
                ['throwable' => $throwable],
            );
        } catch (ViewNotFoundException | StrategyNotFoundException) {
            return $this->view->render('Views/index', [
                'message' => $throwable->getMessage(),
                'trace' => str_replace(["\n", ": "], ["\n\n", ":\n"], $throwable->getTraceAsString()),
                'type' => $throwable::class,
                'statusCode' => 500,
                'xDebugTag' => $this->debugTagStorage->getTag(),
                'showTrace' => (int) getenv('APP_DEBUG') === 1,
            ]);
        }
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }
}
