<?php

declare(strict_types=1);

namespace Atlas\Logger;

use Atlas\EventDispatcher\ClosureObserver;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Message;
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

    protected function formatMessage(string $level, string $message): string
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
            LogContextEvent::ATTACH_CONTEXT->value => function (Message $message) {
                $this->context[$message->message] = $message->message;
            },
            LogContextEvent::DETACH_CONTEXT->value => function (Message $message) {
                if (isset($this->context[$message->message]) === false) {
                    return;
                }

                unset($this->context[$message->message]);
            },
            LogContextEvent::FLUSH_CONTEXT->value => function () {
                $this->context = [];
            },
            LogContextEvent::ATTACH_EXTRAS->value => function (Message $message) {
                $this->extras = $message->message;
            },
            LogContextEvent::FLUSH_EXTRAS->value => function () {
                $this->extras = null;
            },
            LogContextEvent::ATTACH_CATEGORY->value => function (Message $message) {
                $this->category = $message->message;
            },
            LogContextEvent::FLUSH_CATEGORY->value => function () {
                $this->category = null;
            },
        ];

        foreach ($listeners as $event => $listener) {
            $this->dispatcher->attach($event, new ClosureObserver($listener));
        }
    }
}
