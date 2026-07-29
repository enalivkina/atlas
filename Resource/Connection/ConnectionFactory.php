<?php

declare(strict_types=1);

namespace Atlas\Resource\Connection;

use Atlas\Resource\Connection\Contract\ConnectionFactoryInterface;
use Atlas\Resource\Connection\Contract\ConnectionInterface;

final class ConnectionFactory implements ConnectionFactoryInterface
{
    public function createConnection(array $config): ConnectionInterface
    {
        return match ($config['driver']) {
            'mysql' => new DbConnection($config),
            'file' => new FileConnection(...$config),
        };
    }
}
