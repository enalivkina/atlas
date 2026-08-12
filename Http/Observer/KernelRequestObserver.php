<?php

namespace Atlas\Http\Observer;

use Atlas\EventDispatcher\Contract\ObserverInterface;
use Atlas\EventDispatcher\Message;

final class KernelRequestObserver implements ObserverInterface
{
    public function observe(Message $event): void {}
}
