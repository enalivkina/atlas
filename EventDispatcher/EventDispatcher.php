<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher;

use Atlas\EventDispatcher\Contract\EventDispatcherInterface;
use Atlas\EventDispatcher\Contract\ObserverInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array Хранит подписки на события в формате [eventName => [observers]]
     */
    private array $observers = [];

    /**
     * @inheritDoc
     */

    public function attach(string $event, ObserverInterface $observer): void
    {
        $this->observers[$event][] = $observer;
    }

    public function trigger(string $event, Message|null $message = null): void
    {
        foreach ($this->observers[$event] ?? [] as $observer) {
            if (method_exists($observer, 'observe') === true) {
                $observer->observe($event);
            }

            if (method_exists($observer, 'handle') === true) {
                $observer->handle($event);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function detach(string $event): void
    {
        if (isset($this->observers[$event]) === false) {
            return;
        }

        $key = array_search($event, $this->observers[$event], true);

        if ($key === false) {
            return;
        }

        unset($this->observers[$event][$key]);
    }
}
