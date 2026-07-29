<?php

declare(strict_types=1);

namespace Atlas\Http\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface HTTPKernelInterface
{
    /**
     * Обработка входящего запроса
     *
     * @return ServerResponseInterface объект ответа
     */
    public function handle(ServerRequestInterface $request): ServerResponseInterface;
}
