<?php

namespace Atlas\Http\ErrorHandler\Strategy;

use Atlas\ConfigurationStorage\ConfigurationStorage;
use Atlas\Http\Exceptions\HttpException;
use Atlas\Logger\DebugTagStorage;
use Atlas\View\ViewInterface;
use Throwable;

final readonly class HtmlRenderingStrategy implements RenderingStrategyInterface
{
    public function __construct(
        private DebugTagStorage $debugTagStorage,
        private ConfigurationStorage $configurationStorage,
        private ViewInterface $view,
    ) {}

    public function execute(Throwable $throwable): string
    {
        $message = $throwable instanceof HttpException === true
            ? json_encode($throwable->getMessage(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $throwable->getMessage();
        $trace = str_replace(["\n", ": "], ["\n\n", ":\n"], $throwable->getTraceAsString());
        $type = $throwable::class;
        $statusCode = $throwable instanceof HttpException === true ? $throwable->getStatusCode() : 500;

        return $this->view->render('error', [
            'message' => $message,
            'trace' => $trace,
            'type' => $type,
            'statusCode' => $statusCode,
            'xDebugTag' => $this->debugTagStorage->getTag(),
            'showTrace' => (int) $this->configurationStorage->getOrDefault('DEBUG', 0) === 1,
        ]);
    }
}
