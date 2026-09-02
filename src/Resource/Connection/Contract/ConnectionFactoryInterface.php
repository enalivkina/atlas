<?php

declare(strict_types=1);

namespace Atlas\Resource\Connection\Contract;

interface ConnectionFactoryInterface
{
    public function createConnection(array $config): DataBaseConnectionInterface;
}
