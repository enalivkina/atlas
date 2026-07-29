<?php

declare(strict_types=1);

namespace Atlas\Logger;

use Atlas\Logger\Enum\LogContextEvent;

final class StdOutLogger extends AbstractLogger
{
    private array $context = [];
    private mixed $extras = null;
    private ?string $category = null;

    /**
     * Поток вывода
     * @var resource
     */
    private $stdOut;

    public function __construct(
        private readonly LogStateProcessor $processor,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->stdOut = fopen('php://stdout', 'w');

        $this->initEvents();
    }

    protected function formatMessage(string $level, mixed $message): string
    {
        $data = $this->processor->process(
            message: $message,
            level: $level,
            category: $this->category,
            context: $this->context,
            extras: $this->extras,
        )->toArray();

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function writeLog(string $log): void
    {
        fwrite($this->stdOut, $log . PHP_EOL);
    }

    private function initEvents(): void
    {
        $listeners = [
            LogContextEvent::ATTACH_CONTEXT->value => function (Message $event) {
                $this->context[$event->message] = $event->message;
            },
            LogContextEvent::DETACH_CONTEXT->value => function (Message $event) {
                if (isset($this->context[$event->message]) === false) {
                    return;
                }

                unset($this->context[$event->message]);
            },
            LogContextEvent::FLUSH_CONTEXT->value => function () {
                $this->context = [];
            },
            LogContextEvent::ATTACH_EXTRAS->value => function (Message $event) {
                $this->extras = $event->message;
            },
            LogContextEvent::FLUSH_EXTRAS->value => function () {
                $this->extras = null;
            },
            LogContextEvent::ATTACH_CATEGORY->value => function (Message $event) {
                $this->category = $event->message;
            },
            LogContextEvent::FLUSH_CATEGORY->value => function () {
                $this->category = null;
            },
        ];

        foreach ($listeners as $event => $listener) {
            $this->dispatcher->attach($event, $listener);
        }
    }
}
