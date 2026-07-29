<?php

declare(strict_types=1);

namespace Atlas\Http\Router\Contract;

use Atlas\Http\Contract\ServerResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    /**
     * @param  ServerRequestInterface $request
     * @param  ServerResponseInterface $response
     * @param  callable $next
     * @return void
     */
    public function __invoke(ServerRequestInterface $request, ServerResponseInterface $response, callable $next): void;
}
