<?php

declare(strict_types=1);

namespace Atlas\Http\Contract;

use Psr\Http\Message\ResponseInterface;

interface ServerResponseInterface extends ResponseInterface
{
    public function send(): void;
}
