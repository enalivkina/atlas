<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher\Contract;

use Atlas\EventDispatcher\Event;

interface ObserverInterface
{
    /**
     * @param Event $event
     */
    public function observe(Event $event): void;
}
