<?php

declare(strict_types=1);

namespace Atlas\Http\ErrorHandler;

use Atlas\Common\Contract\ErrorHandlerInterface;
use Atlas\ConfigurationStorage\ConfigurationStorage;
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
    private array $defaultRenderingStrategies = [
        ContentType::HTML->value => HtmlRenderingStrategy::class,
        ContentType::JSON->value => JsonRenderingStrategy::class,
    ];

    private readonly array $renderingStrategies;

    public function __construct(
        private readonly ViewInterface $view,
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly ConfigurationStorage $configurationStorage,
        private readonly ContainerInterface $container,
        array $renderingStrategies = [],
        private string $mode = ContentType::HTML->value,
    ) {
        $this->renderingStrategies = array_merge($this->defaultRenderingStrategies, $renderingStrategies);
    }

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
            return $this->view->render('@framework/http/error', [
                'message' => $throwable->getMessage(),
                'trace' => str_replace(["\n", ": "], ["\n\n", ":\n"], $throwable->getTraceAsString()),
                'type' => $throwable::class,
                'statusCode' => 500,
                'xDebugTag' => $this->debugTagStorage->getTag(),
                'showTrace' => (int) $this->configurationStorage->getOrDefault('DEBUG', 0) === 1,
            ]);
        }
    }

    public function defineMode(string $mode): void
    {
        $this->mode = $mode;
    }
}
