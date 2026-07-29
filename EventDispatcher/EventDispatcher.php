<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher;

use Atlas\Container\ContainerInterface;
use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Contract\ObserverInterface;
use Exception;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array Хранит подписки на события в формате [eventName => [observers]]
     */
    private array $observers = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        foreach ($config as $eventName => $observers) {
            if (is_array($observers) === false) {
                throw new Exception("Наблюдатели для события {$eventName} должны быть массивом");
            }

            foreach ($observers as $observer) {
                $this->attach($eventName, $observer);
            }
        }
    }

    /**
     * @inheritDoc
     */

    public function attach(string $eventName, string|callable $observer): void
    {
        if (is_callable($observer) === false) {
            $implements = class_implements($observer);

            if ($implements === false || (in_array(ObserverInterface::class, $implements, true)) === false) {
                throw new Exception("Класс {$observer} должен реализовывать " . ObserverInterface::class);
            }
        }

        $this->observers[$eventName][] = $observer;
    }

    public function trigger(string $eventName, Event $event): void
    {
        foreach ($this->observers[$eventName] ?? [] as $observer) {
            if (is_callable($observer) === true) {
                $observer($event);
                continue;
            }

            $instance = $this->container->build($observer);

            if (method_exists($instance, 'observe') === true) {
                $instance->observe($event);
            }

            if (method_exists($instance, 'handle') === true) {
                $instance->handle($event);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function detach(string $eventName, string|callable $observer): void
    {
        if (isset($this->observers[$eventName]) === false) {
            return;
        }

        $key = array_search($observer, $this->observers[$eventName], true);

        if ($key === false) {
            return;
        }

        unset($this->observers[$eventName][$key]);
    }
}
