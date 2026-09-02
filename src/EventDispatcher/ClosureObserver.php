<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher;

use Atlas\EventDispatcher\Contract\ObserverInterface;
use Closure;

final class ClosureObserver implements ObserverInterface
{
    /**
     * @param Closure $listener
     */
    public function __construct(
        private readonly Closure $listener,
    ) {}

    public function observe(Message $event): void
    {
        ($this->listener)($event);
    }
}
