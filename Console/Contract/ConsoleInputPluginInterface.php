<?php

declare(strict_types=1);

namespace Atlas\Console\Contract;

use Atlas\EventDispatcher\Contract\EventDispatcherInterface;

interface ConsoleInputPluginInterface
{
    public function init(ConsoleInputInterface $input, EventDispatcherInterface $dispatcher): void;
}
