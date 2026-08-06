<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher\Contract;

use Atlas\EventDispatcher\Message;

interface ObserverInterface
{
    /**
     * @param Message $event
     */
    public function observe(Message $event): void;
}
