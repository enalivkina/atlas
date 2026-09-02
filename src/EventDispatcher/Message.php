<?php

declare(strict_types=1);

namespace Atlas\EventDispatcher;

final class Message
{
    public function __construct(public readonly mixed $message = null) {}
}
